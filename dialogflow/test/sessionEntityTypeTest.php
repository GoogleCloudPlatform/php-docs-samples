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
 * Unit Tests for session entity type management commands.
 */
class sessionEntityTypeTest extends \PHPUnit_Framework_TestCase
{
    private $projectId;
    private $entityTypeDisplayName;
    private $entityValues;
    private $sessionId;

    public function setUp()
    {
        $this->entityTypeDisplayName = 'fake_display_name_for_testing';
        $this->entityValues = array('fake_entity_value_1', 'fake_entity_value_2');
        $this->sessionId = 'fake_session_for_testing';

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
        $setUpCommand = $application->get('entity-type-create');
        $createCommand = $application->get('session-entity-type-create');
        $listCommand = $application->get('session-entity-type-list');
        $deleteCommand = $application->get('session-entity-type-delete');
        $cleanUpCommand = $application->get('entity-type-delete');
        $this->commandTesterSetUp = new CommandTester($setUpCommand);
        $this->commandTesterCreate = new CommandTester($createCommand);
        $this->commandTesterList = new CommandTester($listCommand);
        $this->commandTesterDelete = new CommandTester($deleteCommand);
        $this->commandTesterCleanUp = new CommandTester($cleanUpCommand);
    }

    public function testCreateSessionEntityType()
    {
        $response = $this->runCommand('entity-type-create',[
            'display-name' => $this->entityTypeDisplayName
        ]);
        $this->runCommand('session-entity-type-create', [
            'entity-type-display-name' => $this->entityTypeDisplayName,
            'entity-values' => $this->entityValues,
            '--session-id' => $this->sessionId
        ]);
        $output = $this->runCommand('session-entity-type-list', [
            '--session-id' => $this->sessionId
        ]);

        $this->assertContains($this->entityTypeDisplayName, $output);

        $response = str_replace(array("\r", "\n"), '', $response);
        $response = explode('/', $response);
        $entityTypeId = end($response);
        return $entityTypeId;
    }

    /** @depends testCreateSessionEntityType */
    public function testDeleteSessionEntityType($entityTypeId)
    {
        $this->runCommand('session-entity-type-delete', [
            'entity-type-display-name' => $this->entityTypeDisplayName,
            '--session-id' => $this->sessionId
        ]);
        $output = $this->runCommand('session-entity-type-list', [
            '--session-id' => $this->sessionId
        ]);
        $this->runCommand('entity-type-delete', [
            'entity-type-id' => $entityTypeId
        ]);
        
        $this->assertNotContains($this->entityTypeDisplayName, $output);
    }

    private function runCommand($commandName, $args=[])
    {
        $application = require __DIR__ . '/../dialogflow.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);
        ob_start();
        $commandTester->execute(
            $args + [
                'project-id' => $this->projectId
            ],
            ['interactive' => false]
        );
        return ob_get_clean();
    }
}
