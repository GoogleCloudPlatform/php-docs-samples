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
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for general pubsub samples.
 */
class PubSubTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $topic;
    private static $subscription;

    public static function setUpBeforeClass(): void
    {
        self::$topic = self::requireEnv('GOOGLE_PUBSUB_TOPIC');
        self::$subscription = self::requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');
    }

    public function testGetSubscriptionPolicy()
    {
        $output = $this->runFunctionSnippet('get_subscription_policy', [
            'projectId' => self::$projectId,
            'subscriptionName' => self::$subscription,
        ]);

        $this->assertStringContainsString('etag', $output);
    }

    public function testGetTopicPolicy()
    {
        $output = $this->runFunctionSnippet('get_topic_policy', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
        ]);

        $this->assertStringContainsString('etag', $output);
    }

    public function testSetSubscriptionPolicy()
    {
        $userEmail = 'betterbrent@google.com';

        $output = $this->runFunctionSnippet('set_subscription_policy', [
            'projectId' => self::$projectId,
            'subscriptionName' => self::$subscription,
            'userEmail' => $userEmail,
        ]);

        $this->assertStringContainsString(
            sprintf('User %s added to policy for %s', $userEmail, self::$subscription),
            $output
        );
    }

    public function testSetTopicPolicy()
    {
        $userEmail = 'betterbrent@google.com';

        $output = $this->runFunctionSnippet('set_topic_policy', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
            'userEmail' => $userEmail,
        ]);

        $this->assertStringContainsString(
            sprintf('User %s added to policy for %s', $userEmail, self::$topic),
            $output
        );
    }

    public function testTestSubscriptionPermissions()
    {
        $output = $this->runFunctionSnippet('test_subscription_permissions', [
            'projectId' => self::$projectId,
            'subscriptionName' => self::$subscription,
        ]);

        $this->assertStringContainsString(
            'Permission: pubsub.subscriptions.consume',
            $output
        );
    }

    public function testTestTopicPermissions()
    {
        $output = $this->runFunctionSnippet('test_topic_permissions', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
        ]);

        $this->assertStringContainsString(
            'Permission: pubsub.topics.attachSubscription',
            $output
        );
    }

    public function testListTopics()
    {
        $output = $this->runFunctionSnippet('list_topics', [
            'projectId' => self::$projectId,
        ]);
        $this->assertRegExp(sprintf('/%s/', self::$topic), $output);
    }

    public function testCreateAndDeleteTopic()
    {
        $topic = 'test-topic-' . rand();
        $output = $this->runFunctionSnippet('create_topic', [
            'projectId' => self::$projectId,
            'topicName' => $topic,
        ]);

        $this->assertRegExp('/Topic created:/', $output);
        $this->assertRegExp(sprintf('/%s/', $topic), $output);

        $output = $this->runFunctionSnippet('delete_topic', [
            'projectId' => self::$projectId,
            'topicName' => $topic,
        ]);

        $this->assertRegExp('/Topic deleted:/', $output);
        $this->assertRegExp(sprintf('/%s/', $topic), $output);
    }

    public function testPublishMessage()
    {
        $output = $this->runFunctionSnippet('publish_message', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
            'message' => 'This is a test message',
        ]);

        $this->assertRegExp('/Message published/', $output);
    }

    public function testListSubscriptions()
    {
        $output = $this->runFunctionSnippet('list_subscriptions', [
            'projectId' => self::$projectId,
        ]);

        $this->assertRegExp(sprintf('/%s/', self::$subscription), $output);
    }

    public function testCreateAndDeleteSubscription()
    {
        $subscription = 'test-subscription-' . rand();
        $output = $this->runFunctionSnippet('create_subscription', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
            'subscriptionName' => $subscription,
        ]);

        $this->assertRegExp('/Subscription created:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            'projectId' => self::$projectId,
            'subscriptionName' => $subscription,
        ]);

        $this->assertRegExp('/Subscription deleted:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDeletePushSubscription()
    {
        $subscription = 'test-subscription-' . rand();
        $fakeUrl = sprintf('https://%s.appspot.com/receive_message', self::$projectId);
        $output = $this->runFunctionSnippet('create_push_subscription', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
            'subscriptionName' => $subscription,
            'endpoint' => $fakeUrl,
        ]);

        $this->assertRegExp('/Subscription created:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('delete_subscription', [
            'projectId' => self::$projectId,
            'subscriptionName' => $subscription,
        ]);

        $this->assertRegExp('/Subscription deleted:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDetachSubscription()
    {
        $subscription = 'testdetachsubsxyz-' . rand();
        $output = $this->runFunctionSnippet('create_subscription', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
            'subscriptionName' => $subscription,
        ]);

        $this->assertRegExp('/Subscription created:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);

        $output = $this->runFunctionSnippet('detach_subscription', [
            'projectId' => self::$projectId,
            'subscriptionName' => $subscription,
        ]);

        $this->assertRegExp('/Subscription detached:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);

        // delete test resource
        $output = $this->runFunctionSnippet('delete_subscription', [
            'projectId' => self::$projectId,
            'subscriptionName' => $subscription,
        ]);

        $this->assertRegExp('/Subscription deleted:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);
    }

    public function testPullMessages()
    {
        $output = $this->runFunctionSnippet('publish_message', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
            'message' => 'This is a test message',
        ]);

        $this->assertRegExp('/Message published/', $output);

        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('pull_messages', [
                'projectId' => self::$projectId,
                'subscriptionName' => self::$subscription,
            ]);
            $this->assertRegExp('/This is a test message/', $output);
        });
    }

    public function testPublishMessageBatch()
    {
        $messageData = uniqid('message-');

        $pid = shell_exec(
            'php ' . __DIR__ . '/../vendor/bin/google-cloud-batch daemon > /dev/null 2>&1 & echo $!'
        );
        putenv('IS_BATCH_DAEMON_RUNNING=true');

        $output = $this->runFunctionSnippet('publish_message_batch', [
            'projectId' => self::$projectId,
            'topicName' => self::$topic,
            'message' => $messageData,
        ]);

        $this->assertRegExp('/Messages enqueued for publication/', $output);

        $this->runEventuallyConsistentTest(function () use ($messageData) {
            $output = $this->runFunctionSnippet('pull_messages', [
                'projectId' => self::$projectId,
                'subscriptionName' => self::$subscription,
            ]);
            $this->assertStringContainsString($messageData, $output);
        });

        shell_exec('kill -9 ' . $pid);
        putenv('IS_BATCH_DAEMON_RUNNING=');
    }
}
