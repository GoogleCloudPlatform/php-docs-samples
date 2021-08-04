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

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for storage IAM.
 */
class IamTest extends TestCase
{
    use TestTrait;

    protected static $storage;
    protected static $user;
    protected static $bucket;
    private static $role = 'roles/storage.objectViewer';

    public static function setUpBeforeClass(): void
    {
        self::$storage = new StorageClient(['projectId' => self::$projectId]);
        self::$user = self::requireEnv('GOOGLE_IAM_USER');
        self::$bucket = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::setUpIam();
    }

    private static function setUpIam()
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

        foreach ($policy['bindings'] as $i => $binding) {
            if (
                $binding['role'] == self::$role &&
                in_array(self::$user, $binding['members'])
            ) {
                unset($policy['bindings'][$i]);
            }
        }

        $iam->setPolicy($policy);
    }

    public function testAddBucketIamMember()
    {
        $output = self::runFunctionSnippet('add_bucket_iam_member', [
            self::$bucket,
            self::$role,
            self::$user,
        ]);
        $outputString = sprintf(
            "Added the following member(s) to role %s for bucket %s\n    %s",
            self::$role,
            self::$bucket,
            self::$user
        );

        $this->assertStringContainsString($outputString, $output);

        $foundRoleMember = false;
        $policy = self::$storage->bucket(self::$bucket)->iam()->policy([
            'requestedPolicyVersion' => 3
        ]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == self::$role) {
                $foundRoleMember = in_array(self::$user, $binding['members']);
                break;
            }
        }
        $this->assertTrue($foundRoleMember);
    }

    public function testAddBucketConditionalIamBinding()
    {
        $title = 'always true';
        $description = 'this condition is always true';
        $expression = '1 < 2';

        $output = self::runFunctionSnippet('add_bucket_conditional_iam_binding', [
            self::$bucket,
            self::$role,
            self::$user,
            $title,
            $description,
            $expression,
        ]);

        $outputString = sprintf(
            'Added the following member(s) with role %s to %s:
    %s
with condition:
    Title: %s
    Description: %s
    Expression: %s
', self::$role, self::$bucket, self::$user, $title, $description, $expression);

        $this->assertEquals($outputString, $output);

        $foundBinding = false;
        $policy = self::$storage->bucket(self::$bucket)->iam()->policy([
            'requestedPolicyVersion' => 3
        ]);
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == self::$role) {
                if (in_array(self::$user, $binding['members']) &&
                    isset($binding['condition']) &&
                    $binding['condition']['title'] == $title &&
                    $binding['condition']['description'] == $description &&
                    $binding['condition']['expression'] == $expression
                ) {
                    $foundBinding = true;
                    break;
                }
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
        $output = self::runFunctionSnippet('view_bucket_iam_members', [
            self::$bucket,
        ]);

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
            'Role: roles/storage.objectViewer
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
        $output = self::runFunctionSnippet('remove_bucket_iam_member', [
            self::$bucket,
            self::$role,
            self::$user,
        ]);

        $expected = sprintf(
            'User %s removed from role %s for bucket %s',
            self::$user,
            self::$role,
            self::$bucket
        );

        $this->assertStringContainsString($expected, $output);

        $foundRoleMember = false;
        $policy = self::$storage->bucket(self::$bucket)->iam()->policy([
            'requestedPolicyVersion' => 3
        ]);
        foreach ($policy['bindings'] as $binding) {
            if (
                $binding['role'] == self::$role
                && empty($binding['condition'])
            ) {
                $foundRoleMember = in_array(self::$user, $binding['members']);
                break;
            }
        }
        $this->assertFalse($foundRoleMember);
    }

    /**
     * @depends testAddBucketConditionalIamBinding
     * @depends testListIamMembers
     */
    public function testRemoveBucketConditionalIamBinding()
    {
        $title = 'always true';
        $description = 'this condition is always true';
        $expression = '1 < 2';

        $output = self::runFunctionSnippet('remove_bucket_conditional_iam_binding', [
            self::$bucket,
            self::$role,
            $title,
            $description,
            $expression
        ]);

        $this->assertStringContainsString(
            'Conditional Binding was removed.',
            $output
        );

        $foundBinding = false;
        $policy = self::$storage->bucket(self::$bucket)->iam()->policy([
            'requestedPolicyVersion' => 3
        ]);
        foreach ($policy['bindings'] as $binding) {
            if (
                $binding['role'] == self::$role
                && isset($binding['condition'])
            ) {
                $condition = $binding['condition'];
                if ($condition['title'] == $title
                     && $condition['description'] == $description
                     && $condition['expression'] == $expression) {
                    $foundRoleMember = true;
                    break;
                }
            }
        }
        $this->assertFalse($foundBinding);
    }

    public function testSetBucketPublicIam()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-public-iam-'));

        $output = self::runFunctionSnippet('set_bucket_public_iam', [
            $bucket->name(),
        ]);

        $this->assertEquals(
            sprintf('Bucket %s is now public', $bucket->name()),
            $output
        );

        $policy = $bucket->iam()->policy();
        $hasBinding = false;
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == 'roles/storage.objectViewer' && $binding['members'] = ['allUsers']) {
                $hasBinding = true;
                break;
            }
        }

        $bucket->delete();

        $this->assertTrue($hasBinding, 'has public viewable iam binding');
    }
}
