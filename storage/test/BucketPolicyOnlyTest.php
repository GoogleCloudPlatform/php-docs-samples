<?php
/**
 * Copyright 2019 Google Inc.
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
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for BucketPolicyOnlyCommand.
 */
class BucketLockCommandTest extends \PHPUnit_Framework_TestCase
{

    protected static $hasCredentials;
    protected $commandTester;
    protected $storage;
    protected $bucket;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        // Sleep to avoid the rate limit for creating/deleting.
        sleep(5 + rand(2, 4));
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('bucket-policy-only'));
        $this->storage = new StorageClient();
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        // Append random because tests for multiple PHP versions were running at the same time.
        $bucketName = 'php-bucket-policy-only-' . time() . '-' . rand(1000, 9999);
        $this->bucket = $this->storage->createBucket($bucketName);
    }

    public function tearDown()
    {
        $this->bucket->delete();
    }

    public function testEnableBucketPolicyOnly()
    {
        $output = $this->runCommand('bucket-policy-only', [
            'project' => self::$projectId,
            'bucket' => $this->bucket,
            '--enable' => true,
        ]);

        $this->assertContains("Bucket Policy Only was enabled for", $output);
    }

    /** @depends testEnableRequesterPays */
    public function testDisableBucketPolicyOnly()
    {
        $output = $this->runCommand('bucket-policy-only', [
            'project' => self::$projectId,
            'bucket' => self::$bucketName,
            '--disable' => true,
        ]);

        $this->assertContains("Bucket Policy Only was disabled for", $output);
    }

    /** depends testDisableRequesterPays */
    public function testGetBucketPolicyOnly()
    {
        $output = $this->runCommand('bucket-policy', [
            'project' => self::$projectId,
            'bucket' => self::$bucketName,
            '--get' => true,
        ]);

        $this->assertContains("Bucket Policy Only is disabled", $output);
    }
}
