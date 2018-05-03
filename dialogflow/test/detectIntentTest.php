<?php
/**
 * Copyright 2018 Google Inc.
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


namespace Google\Cloud\Samples\Dialogflow;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for detect intent commands.
 */
class detectIntentTest extends \PHPUnit_Framework_TestCase
{
    private static $projectId;
    private static $audioFilePath;
    private static $texts = ['hello', 'book a meeting room', 'mountain view'];

    public static function setUpBeforeClass()
    {
        self::$audioFilePath = realpath(__DIR__ . '/../resources/book_a_room.wav');

        if (!self::$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return self::$markTestSkipped('Set the GOOGLE_PROJECT_ID ' .
                'environment variable');
        }

        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            self::$markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
    }

    public function testDetectText()
    {
        $output = $this->runCommand('detect-intent-texts', [
            'texts' => self::$texts
        ]);

        $this->assertContains('date', $output);
    }

    public function testDetectAudio()
    {
        $output = $this->runCommand('detect-intent-audio', [
            'path' => self::$audioFilePath
        ]);

        $this->assertContains('would you like to reserve a room', $output);
    }

    public function testDetectStream()
    {
        if (!extension_loaded('grpc')) {
            $this->markTestSkipped('Must enable grpc extension.');
        }
        $output = $this->runCommand('detect-intent-stream', [
            'path' => self::$audioFilePath
        ]);

        $this->assertContains('would you like to reserve a room', $output);
    }

    private function runCommand($commandName, $args=[])
    {
        $application = require __DIR__ . '/../dialogflow.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);
        ob_start();
        $commandTester->execute(
            $args + [
                'project-id' => self::$projectId
            ],
            ['interactive' => false]
        );
        return ob_get_clean();
    }
}
