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

namespace Google\Cloud\Samples\PubSub\Tests;

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for IamCommand.
 */
class pubsubTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;
    use EventuallyConsistentTestTrait;

    private static $commandFile = __DIR__ . '/../pubsub.php';

    public function testSubscriptionPolicy()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runCommand('iam', [
            '--subscription' => $subscription,
            'project' => self::$projectId,
        ]);

        $this->assertContains('etag', $output);
    }

    public function testTopicPolicy()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runCommand('iam', [
            '--topic' => $topic,
            'project' => self::$projectId,
        ]);

        $this->assertContains('etag', $output);
    }

    public function testCreateSubscriptionPolicy()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');
        $userEmail = 'betterbrent@google.com';

        $output = $this->runCommand('iam', [
            '--subscription' => $subscription,
            '--add-user' => $userEmail,
            'project' => self::$projectId,
        ]);

        $this->assertContains(sprintf('User %s added to policy for %s', $userEmail, $subscription), $output);
    }

    public function testCreateTopicPolicy()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $userEmail = 'betterbrent@google.com';

        $output = $this->runCommand('iam', [
            '--topic' => $topic,
            '--add-user' => $userEmail,
            'project' => self::$projectId,
        ]);

        $this->assertContains(sprintf('User %s added to policy for %s', $userEmail, $topic), $output);
    }

    public function testTestSubscriptionPolicy()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runCommand('iam', [
            '--subscription' => $subscription,
            '--test' => true,
            'project' => self::$projectId,
        ]);

        $this->assertContains('Permission: pubsub.subscriptions.consume', $output);
    }

    public function testTestTopicPolicy()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runCommand('iam', [
            '--topic' => $topic,
            '--test' => true,
            'project' => self::$projectId,
        ]);

        $this->assertContains('Permission: pubsub.topics.attachSubscription', $output);
    }

    public function testListTopics()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runCommand('topic', [
            'project' => self::$projectId,
        ]);
        $this->assertRegExp(sprintf('/%s/', $topic), $output);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Must provide "--create", "--delete" or "message" with topic name
     */
    public function testGetTopicThrowsException()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runCommand('topic', [
            'topic' => $topic,
            'project' => self::$projectId,
        ]);
    }

    public function testCreateAndDeleteTopic()
    {
        $topic = 'test-topic-' . rand();
        $output = $this->runCommand('topic', [
            'topic' => $topic,
                '--create' => true,
                'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Topic created:/', $output);
        $this->assertRegExp(sprintf('/%s/', $topic), $output);

        $output = $this->runCommand('topic', [
            'topic' => $topic,
            '--delete' => true,
            'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Topic deleted:/', $output);
        $this->assertRegExp(sprintf('/%s/', $topic), $output);
    }

    public function testTopicMessage()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');

        $output = $this->runCommand('topic', [
            'topic' => $topic,
            'message' => 'This is a test message',
            'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Message published/', $output);
    }

    public function testListSubscriptions()
    {
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runCommand('subscription', [
            'project' => self::$projectId,
        ]);

        $this->assertRegExp(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDeleteSubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $output = $this->runCommand('subscription', [
            'subscription' => $subscription,
            '--topic' => $topic,
            '--create' => true,
            'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Subscription created:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);

        $output = $this->runCommand('subscription', [
            'subscription' => $subscription,
            '--delete' => true,
            'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Subscription deleted:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);
    }

    public function testCreateAndDeletePushSubscription()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = 'test-subscription-' . rand();
        $fakeUrl = sprintf('https://%s.appspot.com/receive_message', self::$projectId);
        $output = $this->runCommand('subscription', [
            'subscription' => $subscription,
            '--topic' => $topic,
            '--endpoint' => $fakeUrl,
            '--create' => true,
            'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Subscription created:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);

        $output = $this->runCommand('subscription', [
            'subscription' => $subscription,
            '--delete' => true,
            'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Subscription deleted:/', $output);
        $this->assertRegExp(sprintf('/%s/', $subscription), $output);
    }

    public function testPullMessages()
    {
        $topic = $this->requireEnv('GOOGLE_PUBSUB_TOPIC');
        $subscription = $this->requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION');

        $output = $this->runCommand('topic', [
            'topic' => $topic,
            'message' => 'This is a test message',
            'project' => self::$projectId,
        ]);

        $this->assertRegExp('/Message published/', $output);

        $this->runEventuallyConsistentTest(function () use ($subscription) {
            $output = $this->runCommand('subscription', [
                'subscription' => $subscription,
                'project' => self::$projectId,
            ]);
            $this->assertRegExp('/This is a test message/', $output);
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

        $output = $this->runCommand('topic', [
            'project' => self::$projectId,
            'topic' => $topic,
            'message' => $messageData,
            '--batch' => true
        ]);

        $this->assertRegExp('/Messages enqueued for publication/', $output);

        $this->runEventuallyConsistentTest(function () use ($subscription, $messageData) {
            $output = $this->runCommand('subscription', [
                'subscription' => $subscription,
                'project' => self::$projectId,
            ]);
            $this->assertContains($messageData, $output);
        });

        shell_exec('kill -9 ' . $pid);
        putenv('IS_BATCH_DAEMON_RUNNING=');
    }
}
