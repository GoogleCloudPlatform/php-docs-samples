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
use Google\Cloud\Core\Iam\PolicyBuilder;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for IamCommand.
 */
class IamCommandTest extends TestCase
{
    use TestTrait;

    static protected $storage;
    static protected $user;
    static protected $bucket;
    protected $commandTester;

    public static function setUpBeforeClass()
    {
        self::$storage = new StorageClient();
        self::$user = self::requireEnv('GOOGLE_IAM_USER');
        self::$bucket = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::cleanUpIam();
    }

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('iam'));
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
            if (in_array($binding['role'], $roles) && in_array(self::$user, $binding['members'])) {
                unset($policy['bindings'][$i]);
            }
        }

        $iam->setPolicy($policy);
    }

    public function testAddBucketIamMember()
    {
        $bucket = self::$bucket;
        $role = 'roles/storage.objectViewer';
        $user = self::$user;

        $this->commandTester->execute(
            [
                'bucket' => $bucket,
                '--role' => $role,
                '--add-member' => [$user],
            ],
            ['interactive' => false]
        );

        $outputString = <<<EOF
Added the following member(s) to role $role for bucket $bucket
    $user

EOF;
        $this->expectOutputString($outputString);

        $foundRoleMember = false;
        $policy = self::$storage->bucket($bucket)->iam()->policy(['requestedPolicyVersion' => 3]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundRoleMember = in_array($user, $binding['members']);
                break;
            }
        }
        $this->assertTrue($foundRoleMember);
    }

    public function testAddBucketConditionalIamBinding() {
        $bucket = self::$bucket;
        $role = 'roles/storage.objectCreator';
        $user = self::$user;
        $title = 'always true';
        $description = 'this condition is always true';
        $expression = '1 < 2';

        $this->commandTester->execute(
            [
                'bucket' => $bucket,
                '--role' => $role,
                '--add-member' => [$user],
                '--title' => $title,
                '--description' => $description,
                '--expression' => $expression,
            ],
            ['interactive' => false]
        );

        $outputString = <<<EOF
Added the following member(s) with role $role to $bucket:
    $user
with condition:
    Title: $title
    Description: $description
    Expression: $expression

EOF;
        $this->expectOutputString($outputString);

        $foundBinding = false;
        $policy = self::$storage->bucket($bucket)->iam()->policy(['requestedPolicyVersion' => 3]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundBinding =
                    in_array($user, $binding['members']) &&
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
        $bucket = self::$bucket;
        $user = self::$user;
        $this->commandTester->execute(
            [
                'bucket' => $bucket,
            ],
            ['interactive' => false]
        );

        $output = $this->getActualOutput();

        $this->expectOutputRegex("/Printing Bucket IAM members for Bucket: $bucket/");

        $binding = <<<EOF
Role: roles/storage.objectViewer
Members:
  $user

EOF;
        $this->assertStringContainsString($binding, $output);

        $bindingWithCondition = <<<EOF
Role: roles/storage.objectCreator
Members:
  $user
  with condition:
    Title: always true
    Description: this condition is always true
    Expression: 1 < 2
EOF;
        $this->assertStringContainsString($bindingWithCondition, $output);
    }

    /**
     * @depends testAddBucketIamMember
     * @depends testAddBucketConditionalIamBinding
     * @depends testListIamMembers
     */
    public function testRemoveBucketIamMember()
    {
        $bucket = self::$bucket;
        $role = 'roles/storage.objectViewer';
        $user = self::$user;
        $this->commandTester->execute(
            [
                'bucket' => $bucket,
                '--role' => 'roles/storage.objectViewer',
                '--remove-member' => $user,
            ],
            ['interactive' => false]
        );

        $outputString = <<<EOF
User $user removed from role $role for bucket $bucket

EOF;
        $this->expectOutputString($outputString);

        $foundRoleMember = false;
        $policy = self::$storage->bucket($bucket)->iam()->policy(['requestedPolicyVersion' => 3]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundRoleMember = in_array($user, $binding['members']);
                break;
            }
        }
        $this->assertFalse($foundRoleMember);
    }
}
