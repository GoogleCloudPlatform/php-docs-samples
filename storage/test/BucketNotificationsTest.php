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

use Google\Cloud\Core\Iam\PolicyBuilder;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for commands to publish storage notifications.
 */
class BucketNotificationsTest extends TestCase
{
    use TestTrait;

    private static $bucketName;
    private $topicName;
    private $storage;
    private $topic;
    private $bucket;

    public function setUp(): void
    {
        $this->storage = new StorageClient();
        // Append random because tests for multiple PHP versions were running at the same time.
        $uniqueName = sprintf('%s-%s', date_create()->format('Uv'), rand(1000, 9999));
        self::$bucketName = 'php-bucket-lock-' . $uniqueName;
        $this->bucket = $this->storage->createBucket(self::$bucketName);
        // Create topic to publish messages
        $pubSub = new PubSubClient();
        $this->topicName = 'php-storage-bucket-notification-test-topic' . $uniqueName;
        $this->topic = $pubSub->createTopic($this->topicName);
        // Allow IAM role roles/pubsub.publisher to project's GCS Service Agent on the target PubSubTopic
        $serviceAccountEmail = $this->storage->getServiceAccount();
        $iam = $this->topic->iam();
        $updatedPolicy = (new PolicyBuilder($iam->policy()))
            ->addBinding('roles/pubsub.publisher', [
                "serviceAccount:$serviceAccountEmail",
            ])
            ->result();
        $iam->setPolicy($updatedPolicy);
    }

    public function tearDown(): void
    {
        $this->bucket->delete();
        $this->topic->delete();
    }

    public function testCreateBucketNotification()
    {
        $output = $this->runFunctionSnippet('create_bucket_notification', [self::$bucketName, $this->topicName]);

        $this->assertStringContainsString('Successfully created notification', $output);
        $this->assertStringContainsString(self::$bucketName, $output);
        $this->assertStringContainsString($this->topicName, $output);
    }
}
