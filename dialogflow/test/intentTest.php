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
 * Unit Tests for intent management commands.
 */
class intentTest extends \PHPUnit_Framework_TestCase
{
    private $projectId;
    private $displayName;
    private $messageTexts;
    private $trainingPhraseParts;

    public function setUp()
    {
        $this->displayName = 'fake_display_name_for_testing';
        $this->messageTexts = array('fake_message_for_testing_1', 'fake_message_for_testing_2');
        $this->trainingPhraseParts = array('fake_phrase_1', 'fake_phrase_2');

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
        $createCommand = $application->get('intent-create');
        $listCommand = $application->get('intent-list');
        $deleteCommand = $application->get('intent-delete');
        $this->commandTesterCreate = new CommandTester($createCommand);
        $this->commandTesterList = new CommandTester($listCommand);
        $this->commandTesterDelete = new CommandTester($deleteCommand);
    }

    public function testCreateIntent()
    {
        ob_start();
        $this->commandTesterCreate->execute(
            [
                'project-id' => $this->projectId,
                'display-name' => $this->displayName,
                '--training-phrases-parts' => $this->trainingPhraseParts,
                '--message-texts' => $this->messageTexts
            ],
            ['interactive' => false]
        );
        $response = ob_get_clean();
        $response = str_replace(array("\r", "\n"), '', $response);
        $response = explode('/', $response);
        $intentId = end($response);

        ob_start();
        $this->commandTesterList->execute(
            [
                'project-id' => $this->projectId
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertContains($this->displayName, $output);
        return $intentId;
    }

    /**
    * @depends testCreateIntent
    */
    public function testDeleteIntent($intentId)
    {
        $this->commandTesterDelete->execute(
            [
                'project-id' => $this->projectId,
                'intent-id' => $intentId
            ],
            ['interactive' => false]
        );
        
        ob_start();
        $this->commandTesterList->execute(
            [
                'project-id' => $this->projectId
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertNotContains($this->displayName, $output);
    }
}
