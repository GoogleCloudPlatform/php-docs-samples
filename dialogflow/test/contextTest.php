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
 * Unit Tests for context management commands.
 */
class contextTest extends \PHPUnit_Framework_TestCase
{
    private $projectId;
    private $sessionId;
    private $contextId;

    public function setUp()
    {
        $this->sessionId = 'fake_session_for_testing';
        $this->contextId = 'fake_context_for_testing';

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
        $createCommand = $application->get('context-create');
        $listCommand = $application->get('context-list');
        $deleteCommand = $application->get('context-delete');
        $this->commandTesterCreate = new CommandTester($createCommand);
        $this->commandTesterList = new CommandTester($listCommand);
        $this->commandTesterDelete = new CommandTester($deleteCommand);
    }

    public function testCreateContext()
    {
        $this->commandTesterCreate->execute(
            [
                'project-id' => $this->projectId,
                'context-id' => $this->contextId,
                '--session-id' => $this->sessionId,
            ],
            ['interactive' => false]
        );

        ob_start();
        $this->commandTesterList->execute(
            [
                'project-id' => $this->projectId,
                '--session-id' => $this->sessionId,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertContains($this->contextId, $output);
    }

    public function testDeleteContext()
    {
        $this->commandTesterDelete->execute(
            [
                'project-id' => $this->projectId,
                'context-id' => $this->contextId,
                '--session-id' => $this->sessionId,
            ],
            ['interactive' => false]
        );

        ob_start();
        $this->commandTesterList->execute(
            [
                'project-id' => $this->projectId,
                '--session-id' => $this->sessionId,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertNotContains($this->contextId, $output);
    }
}
