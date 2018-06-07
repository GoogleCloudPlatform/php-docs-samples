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

use Google\Cloud\Samples\Storage\BucketsCommand;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for BucketsCommand.
 */
class BucketsCommandTest extends \PHPUnit_Framework_TestCase
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
        $this->commandTester = new CommandTester($application->get('buckets'));
        $this->storage = new StorageClient();
    }

    public function testListBuckets()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        $this->commandTester->execute(
            [],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/Bucket:/");
    }

    public function testCreateAndDeleteBuckets()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        $bucketName = 'test-bucket-' . time();
        $bucket = $this->storage->bucket($bucketName);

        $this->assertFalse($bucket->exists());

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--create' => true,
            ],
            ['interactive' => false]
        );

        $bucket->reload();
        $this->assertTrue($bucket->exists());

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--delete' => true,
            ],
            ['interactive' => false]
        );

        $this->assertFalse($bucket->exists());

        $outputString = <<<EOF
Bucket created: $bucketName
Bucket deleted: $bucketName

EOF;
        $this->expectOutputString($outputString);
    }
}
