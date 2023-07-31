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
        $output = $this->runFunctionSnippet(
            'create_bucket_notifications',
            [
                self::$bucketName,
                $this->topicName,
            ]
        );

        // first notification has id 1
        $this->assertStringContainsString(sprintf(
            'Successfully created notification with ID 1 for bucket %s in topic %s',
            self::$bucketName,
            $this->topicName
        ), $output);
    }

    public function testListBucketNotification()
    {
        // create a notification before listing
        $output = $this->runFunctionSnippet(
            'create_bucket_notifications',
            [
                self::$bucketName,
                $this->topicName,
            ]
        );

        $output .= $this->runFunctionSnippet(
            'list_bucket_notifications',
            [
                self::$bucketName,
            ]
        );

        // first notification has id 1
        $this->assertStringContainsString('Found notification with id 1', $output);
        $this->assertStringContainsString(sprintf(
            'Listed 1 notifications of storage bucket %s.',
            self::$bucketName,
        ), $output);
    }

    public function testPrintPubsubBucketNotification()
    {
        // create a notification before printing
        $output = $this->runFunctionSnippet(
            'create_bucket_notifications',
            [
                self::$bucketName,
                $this->topicName,
            ]
        );
        // first notification has id 1
        $notificationId = '1';

        $output .= $this->runFunctionSnippet(
            'print_pubsub_bucket_notification',
            [
                self::$bucketName,
                $notificationId,
            ]
        );

        $topicName = sprintf(
            '//pubsub.googleapis.com/projects/%s/topics/%s',
            getenv('GOOGLE_PROJECT_ID'),
            $this->topicName
        );

        $this->assertStringContainsString(
            sprintf(
                <<<EOF
          Notification ID: %s
          Topic Name: %s
          Event Types: %s
          Custom Attributes: %s
          Payload Format: %s
          Blob Name Prefix: %s
          Etag: %s
          Self Link: https://www.googleapis.com/storage/v1/b/%s/notificationConfigs/%s
          EOF . PHP_EOL,
                $notificationId,
                $topicName,
                '',
                '',
                'JSON_API_V1',
                '',
                $notificationId,
                self::$bucketName,
                $notificationId
            ),
            $output
        );
    }

    public function testDeleteBucketNotifications()
    {
        // create a notification before deleting
        $this->runFunctionSnippet(
            'create_bucket_notifications',
            [
                self::$bucketName,
                $this->topicName,
            ]
        );

        $output = $this->runFunctionSnippet(
            'list_bucket_notifications',
            [
                self::$bucketName,
            ]
        );
        $this->assertStringContainsString('Found notification with id 1', $output);

        // first notification has id 1
        $notificationId = '1';

        $output = $this->runFunctionSnippet(
            'delete_bucket_notifications',
            [
                self::$bucketName,
                $notificationId
            ]
        );

        $output .= $this->runFunctionSnippet(
            'list_bucket_notifications',
            [
                self::$bucketName,
            ]
        );
        $this->assertStringContainsString('Successfully deleted notification with ID ' . $notificationId, $output);
        $this->assertStringContainsString('Listed 0 notifications of storage bucket', $output);
    }
}
