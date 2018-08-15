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

use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Samples\Storage\BucketDefaultAclCommand;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for BucketDefaultAclCommand.
 */
class BucketDefaultAclCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected $commandTester;
    protected $storage;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('bucket-default-acl'));
        $this->storage = new StorageClient();
    }

    public function testBucketDefaultAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/: OWNER/");
    }

    public function testManageBucketDefaultAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }

        $bucket = $this->storage->bucket($bucketName);
        $acl = $bucket->defaultAcl();

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
                '--create' => true
            ],
            ['interactive' => false]
        );

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers'
            ],
            ['interactive' => false]
        );

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
                '--delete' => true
            ],
            ['interactive' => false]
        );

        try {
            $acl->get(['entity' => 'allAuthenticatedUsers']);
            $this->fail();
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }

        $bucketUrl = sprintf('gs://%s', $bucketName);
        $outputString = <<<EOF
Added allAuthenticatedUsers (READER) to $bucketUrl default ACL
allAuthenticatedUsers: READER
Deleted allAuthenticatedUsers from $bucketUrl default ACL

EOF;
        $this->expectOutputString($outputString);
    }
}
