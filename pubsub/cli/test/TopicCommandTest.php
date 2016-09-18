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

use Google\Cloud\Samples\PubSub\TopicCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for TopicCommand.
 */
class TopicCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function testListTopics()
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
        $application->add(new TopicCommand());
        $commandTester = new CommandTester($application->get('topic'));
        $commandTester->execute(
            [
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/%s/', $topic));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Must provide "--create", "--delete" or "message" with topic name
     */
    public function testGetTopicThrowsException()
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
        $application->add(new TopicCommand());
        $commandTester = new CommandTester($application->get('topic'));
        $commandTester->execute(
            [
                'topic' => $topic,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );
    }

    public function testCreateAndDeleteTopic()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('No project ID');
        }
        $topic = 'test-topic-' . rand();
        $application = new Application();
        $application->add(new TopicCommand());
        $commandTester = new CommandTester($application->get('topic'));
        $commandTester->execute(
            [
                'topic' => $topic,
                '--create' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Topic created:/');
        $this->expectOutputRegex(sprintf('/%s/', $topic));

        $commandTester->execute(
            [
                'topic' => $topic,
                '--delete' => true,
                '--project' => $projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Topic deleted:/');
        $this->expectOutputRegex(sprintf('/%s/', $topic));
    }

    public function testTopicMessage()
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
        $application->add(new TopicCommand());
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
    }
}
