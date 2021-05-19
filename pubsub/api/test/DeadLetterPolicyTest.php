<?php
/**
 * Copyright 2020 Google LLC
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

use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for dead letter policy samples.
 */
class DeadLetterPolicyTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;
    use EventuallyConsistentTestTrait;

    private static $commandFile = __DIR__ . '/../pubsub.php';

    private static $client;

    private static $topicName;
    private static $subscriptionName;
    private static $deadLetterTopicName;
    private static $deadLetterTopic2Name;

    private static $topic;
    private static $subscription;
    private static $deadLetterTopic;
    private static $deadLetterTopic2;

    public static function setUpBeforeClass(): void
    {
        self::$topicName = 'php-dlq-topic-' . time() . '-' . rand(1000, 9999);
        self::$subscriptionName = 'php-dlq-sub-' . time() . '-' . rand(1000, 9999);
        self::$deadLetterTopicName = 'php-dlq-topic-dl-' . time() . '-' . rand(1000, 9999);
        self::$deadLetterTopic2Name = 'php-dlq-topic-dl-' . time() . '-' . rand(1000, 9999);

        self::$client = new PubSubClient();
        self::$topic = self::$client->createTopic(self::$topicName);
        self::$deadLetterTopic = self::$client->createTopic(self::$deadLetterTopicName);
        self::$deadLetterTopic2 = self::$client->createTopic(self::$deadLetterTopic2Name);
        self::$subscription = self::$client->subscription(self::$subscriptionName);
    }

    public static function tearDownAfterClass(): void
    {
        self::$topic->delete();
        self::$subscription->delete();
        self::$deadLetterTopic->delete();
        self::$deadLetterTopic2->delete();
    }

    public function testCreateDeadLetterSubscription()
    {
        $output = $this->runFunctionSnippet('dead_letter_create_subscription', [
            self::$projectId,
            self::$topicName,
            self::$subscriptionName,
            self::$deadLetterTopicName,
        ]);

        $this->assertEquals(
            trim(sprintf(
                'Subscription %s created with dead letter topic %s',
                self::$subscription->name(),
                self::$deadLetterTopic->name()
            )),
            trim($output)
        );
    }

    /**
     * @depends testCreateDeadLetterSubscription
     */
    public function testUpdateDeadLetterSubscription()
    {
        $output = $this->runFunctionSnippet('dead_letter_update_subscription', [
            self::$projectId,
            self::$topicName,
            self::$subscriptionName,
            self::$deadLetterTopic2Name,
        ]);

        $this->assertEquals(
            trim(sprintf(
                'Subscription %s updated with dead letter topic %s',
                self::$subscription->name(),
                self::$deadLetterTopic2->name()
            )),
            trim($output)
        );
    }

    /**
     * @depends testUpdateDeadLetterSubscription
     */
    public function testDeadLetterDeliveryAttempts()
    {
        $message = 'hello world';

        $output = $this->runFunctionSnippet('dead_letter_delivery_attempt', [
            self::$projectId,
            self::$topicName,
            self::$subscriptionName,
            $message
        ]);

        $this->assertEquals(
            trim(sprintf(
                'Received message %s' . PHP_EOL . 'Delivery attempt 1' . PHP_EOL . 'Done',
                $message
            )),
            trim($output)
        );
    }

    /**
     * @depends testDeadLetterDeliveryAttempts
     */
    public function testDeadLetterRemove()
    {
        $output = $this->runFunctionSnippet('dead_letter_remove', [
            self::$projectId,
            self::$topicName,
            self::$subscriptionName,
        ]);

        $this->assertEquals(
            trim(sprintf(
                'Removed dead letter topic from subscription %s',
                self::$subscription->name()
            )),
            trim($output)
        );

        self::$subscription->reload();

        $this->assertArrayNotHasKey('deadLetterPolicy', self::$subscription->info());
    }
}
