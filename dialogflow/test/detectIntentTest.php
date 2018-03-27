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
    private $projectId;
    private $texts;
    private $audioFilePath;

    public function setUp()
    {
        $this->texts = array('hello', 'book a meeting room', 'mountain view');
        $this->audioFilePath = __DIR__ . '/data/book_a_room.wav';

        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID ' .
                'environment variable');
        }
        $this->projectId = $projectId;

        if (!$creds = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }

        $application = require __DIR__ . '/../dialogflow.php';
        $textCommand = $application->get('detect-intent-texts');
        $audioCommand = $application->get('detect-intent-audio');
        $streamCommand = $application->get('detect-intent-stream');
        $this->commandTesterText = new CommandTester($textCommand);
        $this->commandTesterAudio = new CommandTester($audioCommand);
        $this->commandTesterStream = new CommandTester($streamCommand);
    }

    public function testDetectText()
    {
        ob_start();
        $this->commandTesterText->execute(
            [
                'project-id' => $this->projectId,
                'texts' => $this->texts,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertContains('date', $output);
    }

    public function testDetectAudio()
    {
        ob_start();
        $this->commandTesterAudio->execute(
            [
                'project-id' => $this->projectId,
                'path' => $this->audioFilePath,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertContains('would you like to reserve a room', $output);
    }

    public function testDetectStream()
    {
        ob_start();
        $this->commandTesterStream->execute(
            [
                'project-id' => $this->projectId,
                'path' => $this->audioFilePath,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertContains('would you like to reserve a room', $output);
    }
}
