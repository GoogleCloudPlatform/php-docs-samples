<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\Samples\Storage\IamCommand;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for IamCommand.
 */
class IamCommandTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    protected static $storage;
    protected static $user;
    protected static $bucket;
    private static $commandFile = __DIR__ . '/../storage.php';

    public static function setUpBeforeClass()
    {
        self::$storage = new StorageClient();
        self::$user = self::requireEnv('GOOGLE_IAM_USER');
        self::$bucket = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::cleanUpIam();
    }

    private static function cleanUpIam()
    {
        $bucket = self::$storage->bucket(self::$bucket);

        $bucket->update([
            'iamConfiguration' => [
                'uniformBucketLevelAccess' => [
                    'enabled' => true
                ],
            ]
        ]);

        $iam = $bucket->iam();

        $policy = $iam->policy(['requestedPolicyVersion' => 3]);
        $roles = ['roles/storage.objectViewer', 'roles/storage.objectCreator'];

        foreach ($policy['bindings'] as $i => $binding) {
            if (
                in_array($binding['role'], $roles) &&
                in_array(self::$user, $binding['members'])
            ) {
                unset($policy['bindings'][$i]);
            }
        }

        $iam->setPolicy($policy);
    }

    public function testAddBucketIamMember()
    {
        $role = 'roles/storage.objectViewer';
        $output = $this->runCommand('iam', [
            'bucket' => self::$bucket,
            '--role' => $role,
            '--add-member' => [self::$user],
        ]);

        $outputString = sprintf(
            'Added the following member(s) to role %s for bucket %s
    %s', $role, self::$bucket, self::$user);

        $this->assertStringContainsString($outputString, $output);

        $foundRoleMember = false;
        $policy = self::$storage->bucket(self::$bucket)->iam()->policy([
            'requestedPolicyVersion' => 3
        ]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundRoleMember = in_array(self::$user, $binding['members']);
                break;
            }
        }
        $this->assertTrue($foundRoleMember);
    }

    public function testAddBucketConditionalIamBinding()
    {
        $role = 'roles/storage.objectCreator';
        $title = 'always true';
        $description = 'this condition is always true';
        $expression = '1 < 2';

        $output = $this->runCommand('iam', [
            'bucket' => self::$bucket,
            '--role' => $role,
            '--add-member' => [self::$user],
            '--title' => $title,
            '--description' => $description,
            '--expression' => $expression,
        ]);

        $outputString = sprintf(
            'Added the following member(s) with role %s to %s:
    %s
with condition:
    Title: %s
    Description: %s
    Expression: %s
', $role, self::$bucket, self::$user, $title, $description, $expression);

        $this->assertEquals($outputString, $output);

        $foundBinding = false;
        $policy = self::$storage->bucket(self::$bucket)->iam()->policy([
            'requestedPolicyVersion' => 3
        ]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundBinding =
                    in_array(self::$user, $binding['members']) &&
                    isset($binding['condition']) &&
                    $binding['condition']['title'] == $title &&
                    $binding['condition']['description'] == $description &&
                    $binding['condition']['expression'] == $expression;
                break;
            }
        }
        $this->assertTrue($foundBinding);
    }

    /**
     * @depends testAddBucketIamMember
     * @depends testAddBucketConditionalIamBinding
     */
    public function testListIamMembers()
    {
        $output = $this->runCommand('iam', ['bucket' => self::$bucket]);

        $this->assertStringContainsString(
            'Printing Bucket IAM members for Bucket: ' . self::$bucket,
            $output
        );

        $binding = sprintf('/
Role: roles\/storage.objectViewer
Members:(.*)
  %s

/', self::$user);
        $this->assertRegexp($binding, $output);

        $bindingWithCondition = sprintf(
            'Role: roles/storage.objectCreator
Members:
  %s
  with condition:
    Title: always true
    Description: this condition is always true
    Expression: 1 < 2
', self::$user);
        $this->assertStringContainsString($bindingWithCondition, $output);
    }

    /**
     * @depends testAddBucketIamMember
     * @depends testAddBucketConditionalIamBinding
     * @depends testListIamMembers
     */
    public function testRemoveBucketIamMember()
    {
        $role = 'roles/storage.objectViewer';
        $output = $this->runCommand('iam', [
            'bucket' => self::$bucket,
            '--role' => 'roles/storage.objectViewer',
            '--remove-member' => self::$user,
        ]);

        $outputString = sprintf(
            'User %s removed from role %s for bucket %s',
            self::$user,
            $role,
            self::$bucket
        );

        $this->assertStringContainsString($outputString, $output);

        $foundRoleMember = false;
        $policy = self::$storage->bucket(self::$bucket)->iam()->policy([
            'requestedPolicyVersion' => 3
        ]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundRoleMember = in_array(self::$user, $binding['members']);
                break;
            }
        }
        $this->assertFalse($foundRoleMember);
    }
}
