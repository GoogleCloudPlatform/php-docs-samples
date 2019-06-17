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

    protected $commandTester;
    protected $storage;
    protected $user;
    protected $bucket;

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('iam'));
        $this->storage = new StorageClient();
        $this->user = $this->requireEnv('GOOGLE_IAM_USER');
        $this->bucket = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
    }

    public function testAddBucketIamMember()
    {
        $bucket = $this->bucket;
        $role = 'roles/storage.objectViewer';
        $user = $this->user;

        // clean up bucket IAM policy
        $policy = $this->storage->bucket($bucket)->iam()->policy();
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role && in_array($user, $binding['members'])) {
                $policyBuilder = new PolicyBuilder($policy);
                $policyBuilder->removeBinding($role, [$user]);
                $this->storage->bucket($bucket)->iam()->setPolicy($policyBuilder->result());
                break;
            }
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucket,
                '--role' => $role,
                '--add-member' => $user,
            ],
            ['interactive' => false]
        );

        $outputString = <<<EOF
User $user added to role $role for bucket $bucket

EOF;
        $this->expectOutputString($outputString);

        $foundRoleMember = false;
        $policy = $this->storage->bucket($bucket)->iam()->policy();
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundRoleMember = in_array($user, $binding['members']);
                break;
            }
        }
        $this->assertTrue($foundRoleMember);
    }

    /**
     * @depends testAddBucketIamMember
     */
    public function testListIamMembers()
    {
        $bucket = $this->bucket;
        $role = 'roles/storage.objectViewer';
        $user = $this->user;
        $this->commandTester->execute(
            [
                'bucket' => $bucket,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/Printing Bucket IAM members for Bucket: $bucket/");
        $this->expectOutputRegex("/Role: $role/");
        $this->expectOutputRegex("/$user/");
    }

    /**
     * @depends testAddBucketIamMember
     */
    public function testRemoveBucketIamMember()
    {
        $bucket = $this->bucket;
        $role = 'roles/storage.objectViewer';
        $user = $this->user;
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
        $policy = $this->storage->bucket($bucket)->iam()->policy();
        foreach ($policy['bindings'] as $binding) {
            if ($binding['role'] == $role) {
                $foundRoleMember = in_array($user, $binding['members']);
                break;
            }
        }
        $this->assertFalse($foundRoleMember);
    }
}
