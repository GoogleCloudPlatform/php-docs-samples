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
    }

    public function testCreateEntity()
    {
        $this->runCommand('entity-create', [
            'entity-value' => $this->entityValue1
        ]);
        $this->runCommand('entity-create', [
            'entity-value' => $this->entityValue2,
            'synonyms' => $this->synonyms
        ]);
        $output = $this->runCommand('entity-list');

        $this->assertContains($this->entityValue1, $output);
        $this->assertContains($this->entityValue2, $output);
        foreach ($this->synonyms as $synonym) {
            $this->assertContains($synonym, $output);
        }
    }

    /** @depends testCreateEntity */
    public function testDeleteEntity()
    {
        $this->runCommand('entity-delete', [
            'entity-value' => $this->entityValue1
        ]);
        $this->runCommand('entity-delete', [
            'entity-value' => $this->entityValue2
        ]);
        $output = $this->runCommand('entity-list');

        $this->assertNotContains($this->entityValue1, $output);
        $this->assertNotContains($this->entityValue2, $output);
    }

    private function runCommand($commandName, $args=[])
    {
        $application = require __DIR__ . '/../dialogflow.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);
        ob_start();
        $commandTester->execute(
            $args + [
                'project-id' => $this->projectId,
                'entity-type-id' => $this->entityTypeId
            ],
            ['interactive' => false]
        );
        return ob_get_clean();
    }
}
