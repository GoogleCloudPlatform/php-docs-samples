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

namespace Google\Cloud\Samples\PubSub;

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for general pubsub samples.
 */
class PubSubTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;
    use EventuallyConsistentTestTrait;

    private static $eodSubscriptionId;
    private static $awsRoleArn = 'arn:aws:iam::111111111111:role/fake-role-name';
    private static $gcpServiceAccount = 'fake-service-account@project.iam.gserviceaccount.com';

    public static function setUpBeforeClass(): void
    {
        self::$eodSubscriptionId = 'test-eod-subscription-' . rand();
    }

    public function testSubscriptionPolicy()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runFunctionSnippet('get_subscription_policy', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertStringContainsString('etag', $output);
    }

    public function testTopicPolicy()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runFunctionSnippet('get_topic_policy', [
            self::$projectId,
            $topic,
        ]);

        $this->assertStringContainsString('etag', $output);
    }

    public function testCreateSubscriptionPolicy()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');
        $userEmail = 'betterbrent@google.com';

        $output = $this->runFunctionSnippet('set_subscription_policy', [
            self::$projectId,
            $subscription,
            $userEmail,
        ]);

        $this->assertStringContainsString(
            sprintf('User %s added to policy for %s', $userEmail, $subscription),
            $output
        );
    }

    public function testCreateTopicPolicy()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $userEmail = 'betterbrent@google.com';

        $output = $this->runFunctionSnippet('set_topic_policy', [
            self::$projectId,
            $topic,
            $userEmail,
        ]);

        $this->assertStringContainsString(
            sprintf('User %s added to policy for %s', $userEmail, $topic),
            $output
        );
    }

    public function testTestSubscriptionPolicy()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runFunctionSnippet('test_subscription_permissions', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertStringContainsString(
            'Permission: pubsub.subscriptions.consume',
            $output
        );
    }

    public function testTestTopicPolicy()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runFunctionSnippet('test_topic_permissions', [
            self::$projectId,
            $topic,
        ]);

        $this->assertStringContainsString(
            'Permission: pubsub.topics.attachSubscription',
            $output
        );
    }

    public function testListTopics()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runFunctionSnippet('list_topics', [
            self::$projectId,
        ]);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $topic), $output);
    }

    public function testCreateAndDeleteTopic()
    {
        $topic = 'test-topic-' . rand();
        $output = $this->runFunctionSnippet('create_topic', [
            self::$projectId,
            $topic,
        ]);

        $this->assertMatchesRegularExpression('/Topic created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $topic), $output);

        $output = $this->runFunctionSnippet('delete_topic', [
            self::$projectId,
            $topic,
        ]);

        $this->assertMatchesRegularExpression('/Topic deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $topic), $output);
    }

    public function testTopicMessage()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runFunctionSnippet('publish_message', [
            self::$projectId,
            $topic,
            'This is a test message',
        ]);

        $this->assertMatchesRegularExpression('/Message published/', $output);
    }

    public function testTopicMessageWithRetrySettings()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runFunctionSnippet('publish_with_retry_settings', [
            self::$projectId,
            $topic,
            'This is a test message',
        ]);

        $this->assertMatchesRegularExpression('/Message published with retry settings/', $output);
    }

    public function testTopicMessageWithCompressionEnabled()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runFunctionSnippet('publisher_with_compression', [
            self::$projectId,
            $topic,
            'This is a test message',
        ]);

        $this->assertStringContainsString(
            'Published a compressed message of message ID: ',
            $output
        );
    }

    public function testListSubscriptions()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runFunctionSnippet('list_subscriptions', [
            self::$projectId,
        ]);

        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDeleteSubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $output = $this->runFunctionSnippet('create_subscription', [
            self::$projectId,
            $topic,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDeleteSubscriptionWithFilter()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $filter = 'attributes.author="unknown"';
        $output = $this->runFunctionSnippet('create_subscription_with_filter', [
            self::$projectId,
            $topic,
            $subscription,
            $filter
        ]);
        $this->assertStringContainsString(sprintf(
            'Subscription created: projects/%s/subscriptions/%s',
            self::$projectId,
            $subscription
        ), $output);
        $this->assertStringContainsString('"filter":"attributes.author=\"unknown\""', $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertStringContainsString(sprintf(
            'Subscription deleted: projects/%s/subscriptions/%s',
            self::$projectId,
            $subscription
        ), $output);
    }

    public function testCreateSubscriptionWithExactlyOnceDelivery()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = self::$eodSubscriptionId;

        $output = $this->runFunctionSnippet('create_subscription_with_exactly_once_delivery', [
            self::$projectId,
            $topic,
            $subscription
        ]);

        $this->assertStringContainsString('Subscription created with exactly once delivery status: true', $output);
    }

    public function testCreateAndDeletePushSubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $fakeUrl = sprintf('https://%s.appspot.com/receive_message', self::$projectId);
        $output = $this->runFunctionSnippet('create_push_subscription', [
            self::$projectId,
            $topic,
            $subscription,
            $fakeUrl,
        ]);

        $this->assertMatchesRegularExpression('/Subscription created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDeleteBigQuerySubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $projectId = $this->requireEnv('GOOGLE_PROJECT_ID');
        $table = $projectId . '.' . $this->requireEnv('GOOGLE_PUBSUB_BIGQUERY_TABLE');

        $output = $this->runFunctionSnippet('create_bigquery_subscription', [
            self::$projectId,
            $topic,
            $subscription,
            $table,
        ]);

        $this->assertMatchesRegularExpression('/Subscription created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDeleteStorageSubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $bucket = $this->requireEnv('GOOGLE_PUBSUB_STORAGE_BUCKET');

        $output = $this->runFunctionSnippet('create_cloud_storage_subscription', [
            self::$projectId,
            $topic,
            $subscription,
            $bucket,
        ]);

        $this->assertMatchesRegularExpression('/Subscription created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDetachSubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'testdetachsubsxyz-' . rand();
        $output = $this->runFunctionSnippet('create_subscription', [
            self::$projectId,
            $topic,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('detach_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription detached:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        // delete test resource
        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);
    }

    public function testPullMessages()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runFunctionSnippet('publish_message', [
            self::$projectId,
            $topic,
            'This is a test message',
        ]);

        $this->assertMatchesRegularExpression('/Message published/', $output);

        $this->runEventuallyConsistentTest(function () use ($subscription) {
            $output = $this->runFunctionSnippet('pull_messages', [
                self::$projectId,
                $subscription,
            ]);
            $this->assertMatchesRegularExpression('/This is a test message/', $output);
        });
    }

    public function testPullMessagesBatchPublisher()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');
        $messageData = uniqid('message-');

        $pid = shell_exec(
            'php ' . __DIR__ . '/../vendor/bin/google-cloud-batch daemon > /dev/null 2>&1 & echo $!'
        );
        putenv('IS_BATCH_DAEMON_RUNNING=true');

        $output = $this->runFunctionSnippet('publish_message_batch', [
            self::$projectId,
            $topic,
            $messageData,
        ]);

        $this->assertMatchesRegularExpression('/Messages enqueued for publication/', $output);

        $this->runEventuallyConsistentTest(function () use ($subscription, $messageData) {
            $output = $this->runFunctionSnippet('pull_messages', [
                self::$projectId,
                $subscription,
            ]);
            $this->assertStringContainsString($messageData, $output);
        });

        shell_exec('kill -9 ' . $pid);
        putenv('IS_BATCH_DAEMON_RUNNING=');
    }

    /**
     * @depends testCreateSubscriptionWithExactlyOnceDelivery
     */
    public function testSubscribeExactlyOnceDelivery()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = self::$eodSubscriptionId;

        $output = $this->runFunctionSnippet('publish_message', [
            self::$projectId,
            $topic,
            'This is a test message',
        ]);

        $this->runEventuallyConsistentTest(function () use ($subscription) {
            $output = $this->runFunctionSnippet('subscribe_exactly_once_delivery', [
                self::$projectId,
                $subscription,
            ]);

            // delete the subscription
            $this->runFunctionSnippet('delete_subscription', [
                self::$projectId,
                $subscription,
            ]);

            // There should be at least one acked message
            // pulled from the subscription.
            $this->assertMatchesRegularExpression('/Acknowledged message:/', $output);
        });
    }

    public function testPublishAndSubscribeWithOrderingKeys()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runFunctionSnippet('publish_with_ordering_keys', [
            self::$projectId,
            $topic,
        ]);
        $this->assertMatchesRegularExpression('/Message published/', $output);

        $output = $this->runFunctionSnippet('enable_subscription_ordering', [
            self::$projectId,
            $topic,
            'subscriberWithOrdering' . rand(),
        ]);
        $this->assertMatchesRegularExpression('/Created subscription with ordering/', $output);
        $this->assertMatchesRegularExpression('/\"enableMessageOrdering\":true/', $output);
    }

    public function testCreateAndDeleteUnwrappedSubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $output = $this->runFunctionSnippet('create_unwrapped_push_subscription', [
            self::$projectId,
            $topic,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Unwrapped push subscription created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);

        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);
    }

    public function testSubscriberErrorListener()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();

        // Create subscription
        $output = $this->runFunctionSnippet('create_subscription', [
            self::$projectId,
            $topic,
            $subscription,
        ]);
        $this->assertMatchesRegularExpression('/Subscription created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        // Publish Message
        $testMessage = 'This is a test message';
        $output = $this->runFunctionSnippet('publish_message', [
            self::$projectId,
            $topic,
            $testMessage,
        ]);
        $this->assertMatchesRegularExpression('/Message published/', $output);

        // Pull messages from subscription with error listener
        $output = $this->runFunctionSnippet('subscriber_error_listener', [
            self::$projectId,
            $topic,
            $subscription
        ]);
        // Published message should be received as expected and no exception should be thrown
        $this->assertMatchesRegularExpression(sprintf('/PubSub Message: %s/', $testMessage), $output);
        $this->assertDoesNotMatchRegularExpression('/Exception Message/', $output);

        // Delete subscription
        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subscription,
        ]);
        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subscription), $output);

        // Pull messages from a non-existent subscription with error listener
        $subscription = 'test-subscription-' . rand();
        $output = $this->runFunctionSnippet('subscriber_error_listener', [
            self::$projectId,
            $topic,
            $subscription
        ]);
        // NotFound exception should be caught and printed
        $this->assertMatchesRegularExpression('/Exception Message/', $output);
        $this->assertMatchesRegularExpression(sprintf('/Resource not found \(resource=%s\)/', $subscription), $output);
    }

    public function testOptimisticSubscribe()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subcriptionId = 'test-subscription-' . rand();

        $output = $this->runFunctionSnippet('optimistic_subscribe', [
            self::$projectId,
            $topic,
            $subcriptionId
        ]);
        $this->assertMatchesRegularExpression('/Exception Message/', $output);
        $this->assertMatchesRegularExpression(sprintf('/Resource not found \(resource=%s\)/',  $subcriptionId), $output);

        $testMessage = 'This is a test message';
        $output = $this->runFunctionSnippet('publish_message', [
            self::$projectId,
            $topic,
            $testMessage,
        ]);
        $this->assertMatchesRegularExpression('/Message published/', $output);
        $output = $this->runFunctionSnippet('optimistic_subscribe', [
            self::$projectId,
            $topic,
            $subcriptionId
        ]);
        $this->assertMatchesRegularExpression(sprintf('/PubSub Message: %s/', $testMessage), $output);
        $this->assertDoesNotMatchRegularExpression('/Exception Message/', $output);
        $this->assertDoesNotMatchRegularExpression(sprintf('/Resource not found \(resource=%s\)/', $subcriptionId), $output);

        // Delete subscription
        $output = $this->runFunctionSnippet('delete_subscription', [
            self::$projectId,
            $subcriptionId,
        ]);
        $this->assertMatchesRegularExpression('/Subscription deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $subcriptionId), $output);
    }

    public function testUpdateTopicType()
    {
        $topic = 'test-topic-' . rand();
        $output = $this->runFunctionSnippet('create_topic', [
            self::$projectId,
            $topic,
        ]);

        $this->assertMatchesRegularExpression('/Topic created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $topic), $output);

        $output = $this->runFunctionSnippet('update_topic_type', [
            self::$projectId,
            $topic,
            'arn:aws:kinesis:us-west-2:111111111111:stream/fake-stream-name',
            'arn:aws:kinesis:us-west-2:111111111111:stream/fake-stream-name/consumer/consumer-1:1111111111',
            self::$awsRoleArn,
            self::$gcpServiceAccount
        ]);

        $this->assertMatchesRegularExpression('/Topic updated:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $topic), $output);
    }
  
    public function testCreateTopicWithCloudStorageIngestion()
    {
        $this->requireEnv('PUBSUB_EMULATOR_HOST');

        $topic = 'test-topic-' . rand();
        $output = $this->runFunctionSnippet('create_topic_with_cloud_storage_ingestion', [
            self::$projectId,
            $topic,
            $this->requireEnv('GOOGLE_PUBSUB_STORAGE_BUCKET'),
            'text',
            '1970-01-01T00:00:00Z',
            "\n",
            '**.txt'
        ]);
        $this->assertMatchesRegularExpression('/Topic created:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $topic), $output);

        $output = $this->runFunctionSnippet('delete_topic', [
            self::$projectId,
            $topic,
        ]);
        $this->assertMatchesRegularExpression('/Topic deleted:/', $output);
        $this->assertMatchesRegularExpression(sprintf('/%s/', $topic), $output);
    }
}
