<?php
/**
 * Copyright 2022 Google Inc.
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

namespace Google\Cloud\Samples\Storage;

use Google\Cloud\IAM\PolicyBuilder;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for notification commands.
 */
class BucketNotificationsTest extends TestCase
{
    use TestTrait;

    private static $bucketName;
    protected $storage;
    protected $bucket;
    protected $object;

    public function setUp(): void
    {
        // Sleep to avoid the rate limit for creating/deleting.
        sleep(5 + rand(2, 4));
        $this->storage = new StorageClient();
        // Append random because tests for multiple PHP versions were running at the same time.
        self::$bucketName = 'php-bucket-lock-' . time() . '-' . rand(1000, 9999);
        $this->bucket = $this->storage->createBucket(self::$bucketName);
    }

    public function tearDown(): void
    {
        $this->object && $this->object->delete();
        $this->bucket->delete();
    }

    public function uploadObject()
    {
        $objectName = 'test-object-' . time();
        $file = tempnam(sys_get_temp_dir(), '/tests');
        file_put_contents($file, 'foo' . rand());
        $this->object = $this->bucket->upload($file, [
            'name' => $objectName,
        ]);
        $this->object->reload();
    }

    public function testCreateBucketNotification()
    {
        $topicName = 'test-topic';

        $pubSub = new PubSubClient();
        $serviceAccountEmail = $this->storage->getServiceAccount();
        $topic = $pubSub->topic($topicName);
        $iam = $topic->iam();
        $updatedPolicy = (new PolicyBuilder($iam->policy()))
            ->addBinding('roles/pubsub.publisher', [
                "serviceAccount:$serviceAccountEmail",
            ])
            ->result();
        $iam->setPolicy($updatedPolicy);

        $output = $this->runFunctionSnippet('create_bucket_notification', [self::$bucketName, $topicName]);

        $this->assertStringContainsString('Successfully created notification', $output);
        $this->assertStringContainsString(self::$bucketName, $output);
        $this->assertStringContainsString($topicName, $output);
    }
}
