<?php
/**
 * Copyright 2018 Google LLC
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

namespace Google\Cloud\Samples\Asset;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for asset commands.
 */
class assetTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;
    use EventuallyConsistentTestTrait;

    private static $commandFile = __DIR__ . '/../asset.php';
    private static $storage;
    private static $bucketName;
    private static $bucket;
    private static $topicName;
    private static $pubsub;
    private static $topic;
    private static $feedName;

    public static function setUpBeforeClass()
    {
        self::$storage = new StorageClient();
        self::$bucketName = sprintf('assets-bucket-%s-%s', time(), rand());
        self::$bucket = self::$storage->createBucket(self::$bucketName);
        self::$pubsub = new PubSubClient(['projectId' => self::$projectId]);
        self::$topicName = sprintf('topic-%s-%s', time(), rand());
        self::$topic = self::$pubsub->createTopic(self::$topicName);
    }

    public static function tearDownAfterClass()
    {
        self::$bucket->delete();
        self::$topic->delete();
    }

    // public function testExportAssets()
    // {
    //     $fileName = 'my-assets.txt';
    //     $dumpFilePath = 'gs://' . self::$bucketName . '/' . $fileName;
    //     $output = $this->runCommand('export', [
    //         'project' => self::$projectId,
    //         'filePath' => $dumpFilePath,
    //     ]);
    //     $assetFile = self::$bucket->object($fileName);
    //     $this->assertEquals($assetFile->name(), $fileName);
    //     $assetFile->delete();
    // }

    // public function testBatchGetAssetsHistory()
    // {
    //     $assetName = '//storage.googleapis.com/' . self::$bucketName;

    //     $this->runEventuallyConsistentTest(function () use ($assetName) {
    //         $output = $this->runCommand('batch-get-history', [
    //             'project' => self::$projectId,
    //             'assetNames' => [$assetName],
    //         ]);

    //         $this->assertContains($assetName, $output);
    //     }, 10, true);
    // }

    public function testRealTimeFeed()
    {
        $feedId = sprintf('feed-%s-%s', time(), rand());
        $assetName = '//storage.googleapis.com/' . self::$bucketName;

        $this->runEventuallyConsistentTest(function () use ($feedId, $assetName) {
                $output = $this->runCommand('create-feed', [
                    'parent' => sprintf('projects/%s', self::$projectId),
                    'feedId' => $feedId,
                    'topic' => sprintf('projects/%s/topics/%s', self::$projectId, self::$topicName),
                    'assetNames' => [$assetName],
                ]);
                self::$feedName = $output;

                $this->assertContains($feedId, $output);
            }, 1, true);

        $this->runEventuallyConsistentTest(function () use ($feedId) {
                $output = $this->runCommand('get-feed', [
                    'feedName' => self::$feedName,
                ]);

                $this->assertContains($feedId, $output);
            }, 1, true);

        $this->runEventuallyConsistentTest(function () {
                $output = $this->runCommand('list-feeds', [
                    'parent' => sprintf('projects/%s', self::$projectId),
                ]);

                $this->assertContains('Feeds listed', $output);
            }, 1, true);

        $this->runEventuallyConsistentTest(function () {
                $output = $this->runCommand('update-feed', [
                    'parent' => sprintf('projects/%s', self::$projectId),
                ]);

                $this->assertContains('Feeds listed', $output);
            }, 1, true);

        $this->runEventuallyConsistentTest(function () {
                $output = $this->runCommand('delete-feed', [
                    'feedName' => self::$feedName,
                ]);

                $this->assertContains('Feed deleted', $output);
            }, 1, true);
    }
}
