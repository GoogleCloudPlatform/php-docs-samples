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
 * Unit Tests for entity management commands.
 */
class entityTest extends \PHPUnit_Framework_TestCase
{
    private $projectId;
    private $entityTypeId;
    private $entityValue1;
    private $entityValue2;
    private $synonyms;

    public function setUp()
    {
        $this->entityTypeId = 'e57238e2-e692-44ea-9216-6be1b2332e2a';
        $this->entityValue1 = 'fake_entit_for_testing_1';
        $this->entityValue2 = 'fake_entit_for_testing_2';
        $this->synonyms = array('fake_synonym_for_testing_1', 'fake_synonym_for_testing_2');

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
        $createCommand = $application->get('entity-create');
        $listCommand = $application->get('entity-list');
        $deleteCommand = $application->get('entity-delete');
        $this->commandTesterCreate = new CommandTester($createCommand);
        $this->commandTesterList = new CommandTester($listCommand);
        $this->commandTesterDelete = new CommandTester($deleteCommand);
    }

    public function testCreateEntity()
    {
        $this->commandTesterCreate->execute(
            [
                'project-id' => $this->projectId,
                'entity-type-id' => $this->entityTypeId,
                'entity-value' => $this->entityValue1,
            ],
            ['interactive' => false]
        );

        $this->commandTesterCreate->execute(
            [
                'project-id' => $this->projectId,
                'entity-type-id' => $this->entityTypeId,
                'entity-value' => $this->entityValue2,
                'synonyms' => $this->synonyms,
            ],
            ['interactive' => false]
        );

        ob_start();
        $this->commandTesterList->execute(
            [
                'project-id' => $this->projectId,
                'entity-type-id' => $this->entityTypeId,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertContains($this->entityValue1, $output);
        $this->assertContains($this->entityValue2, $output);
        foreach ($this->synonyms as $synonym) {
            $this->assertContains($synonym, $output);
        }
    }

    public function testDeleteEntity()
    {
        $this->commandTesterDelete->execute(
            [
                'project-id' => $this->projectId,
                'entity-type-id' => $this->entityTypeId,
                'entity-value' => $this->entityValue1,
            ],
            ['interactive' => false]
        );
        $this->commandTesterDelete->execute(
            [
                'project-id' => $this->projectId,
                'entity-type-id' => $this->entityTypeId,
                'entity-value' => $this->entityValue2,
            ],
            ['interactive' => false]
        );

        ob_start();
        $this->commandTesterList->execute(
            [
                'project-id' => $this->projectId,
                'entity-type-id' => $this->entityTypeId,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertNotContains($this->entityValue1, $output);
        $this->assertNotContains($this->entityValue2, $output);
    }
}
