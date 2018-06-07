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
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for IamCommand.
 */
class IamCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected $commandTester;
    protected $storage;
    protected $user;
    protected $bucket;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('iam'));
        $this->storage = new StorageClient();
        if (!$this->user = getenv('GOOGLE_IAM_USER')) {
            $this->markTestSkipped('Set GOOGLE_IAM_USER environemnt variable');
        }
        $this->bucket = getenv('GOOGLE_STORAGE_BUCKET');
    }

    public function testAddBucketIamMember()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        $bucket = $this->bucket;
        $role = 'roles/storage.objectViewer';
        $user = $this->user;
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
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

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
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

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
