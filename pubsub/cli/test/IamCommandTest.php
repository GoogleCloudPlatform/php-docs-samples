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

use Google\Cloud\Samples\PubSub\IamCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for IamCommand.
 */
class IamCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function testSubscriptionPolicy()
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
        $application->add(new IamCommand());
        $commandTester = new CommandTester($application->get('iam'));
        $commandTester->execute(
            [
                '--subscription' => $subscription,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/etag/', $subscription));
    }

    public function testTopicPolicy()
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

        $application = new Application();
        $application->add(new IamCommand());
        $commandTester = new CommandTester($application->get('iam'));
        $commandTester->execute(
            [
                '--topic' => $topic,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/etag/', $topic));
    }

    public function testCreateSubscriptionPolicy()
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
        $userEmail = 'betterbrent@google.com';

        $application = new Application();
        $application->add(new IamCommand());
        $commandTester = new CommandTester($application->get('iam'));
        $commandTester->execute(
            [
                '--subscription' => $subscription,
                '--add-user' => $userEmail,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/User %s added to policy for %s/', $userEmail, $subscription));
    }

    public function testCreateTopicPolicy()
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
        $userEmail = 'betterbrent@google.com';

        $application = new Application();
        $application->add(new IamCommand());
        $commandTester = new CommandTester($application->get('iam'));
        $commandTester->execute(
            [
                '--topic' => $topic,
                '--add-user' => $userEmail,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/User %s added to policy for %s/', $userEmail, $topic));
    }

    public function testTestSubscriptionPolicy()
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
        $application->add(new IamCommand());
        $commandTester = new CommandTester($application->get('iam'));
        $commandTester->execute(
            [
                '--subscription' => $subscription,
                '--test' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Permission: pubsub.subscriptions.consume/');
    }

    public function testTestTopicPolicy()
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

        $application = new Application();
        $application->add(new IamCommand());
        $commandTester = new CommandTester($application->get('iam'));
        $commandTester->execute(
            [
                '--topic' => $topic,
                '--test' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Permission: pubsub.topics.attachSubscription/');
    }
}
