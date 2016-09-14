<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\PubSub\Tests;

use Google\Cloud\Samples\PubSub\SubscriptionCommand;
use Google\Cloud\Samples\PubSub\TopicCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for SubscriptionCommand.
 */
class SubscriptionCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function testListSubscriptions()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$subscription = getenv('GOOGLE_PUBSUB_SUBSCRIPTION')) {
            $this->markTestSkipped('No pubsub subscription name');
        }

        $application = new Application();
        $application->add(new SubscriptionCommand());
        $commandTester = new CommandTester($application->get('subscription'));
        $commandTester->execute(
            [
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/%s/', $subscription));
    }

    public function testCreateAndDeleteSubscription()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$topic = getenv('GOOGLE_PUBSUB_TOPIC')) {
            $this->markTestSkipped('No pubsub topic name');
        }
        $subscription = 'test-subscription-' . rand();
        $application = new Application();
        $application->add(new SubscriptionCommand());
        $commandTester = new CommandTester($application->get('subscription'));
        $commandTester->execute(
            [
                'subscription' => $subscription,
                '--topic' => $topic,
                '--create' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Subscription created:/');
        $this->expectOutputRegex(sprintf('/%s/', $subscription));

        $commandTester->execute(
            [
                'subscription' => $subscription,
                '--delete' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Subscription deleted:/');
        $this->expectOutputRegex(sprintf('/%s/', $subscription));
    }

    public function testCreateAndDeletePushSubscription()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$topic = getenv('GOOGLE_PUBSUB_TOPIC')) {
            $this->markTestSkipped('No pubsub topic name');
        }
        $subscription = 'test-subscription-' . rand();
        $application = new Application();
        $application->add(new SubscriptionCommand());
        $commandTester = new CommandTester($application->get('subscription'));
        $commandTester->execute(
            [
                'subscription' => $subscription,
                '--topic' => $topic,
                '--endpoint' => 'https://example.com/receive_message',
                '--create' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Subscription created:/');
        $this->expectOutputRegex(sprintf('/%s/', $subscription));

        $commandTester->execute(
            [
                'subscription' => $subscription,
                '--delete' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Subscription deleted:/');
        $this->expectOutputRegex(sprintf('/%s/', $subscription));
    }

    public function testPullMessages()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        if (!$topic = getenv('GOOGLE_PUBSUB_TOPIC')) {
            $this->markTestSkipped('No pubsub topic name');
        }
        if (!$subscription = getenv('GOOGLE_PUBSUB_SUBSCRIPTION')) {
            $this->markTestSkipped('No pubsub subscription name');
        }

        $application = new Application();
        $application->add(new TopicCommand());
        $application->add(new SubscriptionCommand());
        $commandTester = new CommandTester($application->get('topic'));
        $commandTester->execute(
            [
                'topic' => $topic,
                'message' => 'This is a test message',
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Message published/');

        $application->add(new SubscriptionCommand());
        $commandTester = new CommandTester($application->get('subscription'));
        $commandTester->execute(
            [
                'subscription' => $subscription,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/This is a test message/');
    }
}
