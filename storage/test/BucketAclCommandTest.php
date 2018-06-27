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
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\Samples\Storage\BucketAclCommand;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for BucketAclCommand.
 */
class BucketAclCommandTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    // $commandFile tells ExecuteCommandTrait the location of the command file
    protected static $commandFile = __DIR__ . '/../storage.php';
    protected static $bucket;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVars();

        // create the bucket
        $storage = new StorageClient();
        $bucketName = sprintf('%s-test-bucket-%s', self::$projectId, time());
        self::$bucket = $storage->createBucket($bucketName);
    }

    public static function tearDownAfterClass()
    {
        self::$bucket->delete();
    }

    public function testBucketAcl()
    {
        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$bucket->name(),
        ]);

        $this->assertRegExp("/: OWNER/", $output);
    }

    public function testManageBucketAcl()
    {
        $acl = self::$bucket->acl();
        $bucketUrl = sprintf('gs://%s', self::$bucket->name());

        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$bucket->name(),
            '--entity' => 'allAuthenticatedUsers',
            '--create' => true,
        ]);

        $expected = "Added allAuthenticatedUsers (READER) to $bucketUrl ACL\n";
        $this->assertEquals($expected, $output);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$bucket->name(),
            '--entity' => 'allAuthenticatedUsers',
        ]);

        $expected = "allAuthenticatedUsers: READER\n";
        $this->assertEquals($expected, $output);

        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$bucket->name(),
            '--entity' => 'allAuthenticatedUsers',
            '--delete' => true,
        ]);

        $expected = "Deleted allAuthenticatedUsers from $bucketUrl ACL\n";
        $this->assertEquals($expected, $output);

        try {
            $acl->get(['entity' => 'allAuthenticatedUsers']);
            $this->fail();
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }
}
