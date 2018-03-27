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
 * Unit Tests for entity type management commands.
 */
class entityTypeTest extends \PHPUnit_Framework_TestCase
{
    private $projectId;
    private $entityTypeDisplayName;

    public function setUp()
    {
        $this->entityTypeDisplayName = 'fake_display_name_for_testing';

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
        $createCommand = $application->get('entity-type-create');
        $listCommand = $application->get('entity-type-list');
        $deleteCommand = $application->get('entity-type-delete');
        $this->commandTesterCreate = new CommandTester($createCommand);
        $this->commandTesterList = new CommandTester($listCommand);
        $this->commandTesterDelete = new CommandTester($deleteCommand);
    }

    public function testCreateEntityType()
    {
        ob_start();
        $this->commandTesterCreate->execute(
            [
                'project-id' => $this->projectId,
                'display-name' => $this->entityTypeDisplayName
            ],
            ['interactive' => false]
        );
        $response = ob_get_clean();
        $response = str_replace(array("\r", "\n"), '', $response);
        $response = explode('/', $response);
        $entityTypeId = end($response);

        ob_start();
        $this->commandTesterList->execute(
            [
                'project-id' => $this->projectId
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertContains($this->entityTypeDisplayName, $output);
        return $entityTypeId;
    }

    /**
    * @depends testCreateEntityType
    */
    public function testDeleteEntityType($entityTypeId)
    {
        $this->commandTesterDelete->execute(
            [
                'project-id' => $this->projectId,
                'entity-type-id' => $entityTypeId
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

        $this->assertNotContains($this->entityTypeDisplayName, $output);
    }
}
