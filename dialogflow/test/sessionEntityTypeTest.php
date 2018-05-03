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
    private static $projectId;
    private static $entityTypeDisplayName;
    private static $sessionId = 'fake_session_for_testing';
    private static $entityValues = ['fake_entity_value_1', 'fake_entity_value_2'];

    public static function setUpBeforeClass()
    {
        if (!self::$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return self::$markTestSkipped('Set the GOOGLE_PROJECT_ID ' .
                'environment variable');
        }

        if (!getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            self::$markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }

        self::$entityTypeDisplayName = 'fake_display_name_for_testing_' . time();
    }

    public function testCreateSessionEntityType()
    {
        $response = $this->runCommand('entity-type-create',[
            'display-name' => self::$entityTypeDisplayName
        ]);
        $this->runCommand('session-entity-type-create', [
            'entity-type-display-name' => self::$entityTypeDisplayName,
            'entity-values' => self::$entityValues,
            '--session-id' => self::$sessionId
        ]);
        $output = $this->runCommand('session-entity-type-list', [
            '--session-id' => self::$sessionId
        ]);

        $this->assertContains(self::$entityTypeDisplayName, $output);

        $response = str_replace(array("\r", "\n"), '', $response);
        $response = explode('/', $response);
        $entityTypeId = end($response);
        return $entityTypeId;
    }

    /** @depends testCreateSessionEntityType */
    public function testDeleteSessionEntityType($entityTypeId)
    {
        $this->runCommand('session-entity-type-delete', [
            'entity-type-display-name' => self::$entityTypeDisplayName,
            '--session-id' => self::$sessionId
        ]);
        $output = $this->runCommand('session-entity-type-list', [
            '--session-id' => self::$sessionId
        ]);
        $this->runCommand('entity-type-delete', [
            'entity-type-id' => $entityTypeId
        ]);

        $this->assertNotContains(self::$entityTypeDisplayName, $output);
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
