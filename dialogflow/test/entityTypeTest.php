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
    private static $projectId;
    private static $entityTypeDisplayName;

    public function setUp()
    {
        self::$entityTypeDisplayName = 'fake_display_name_for_testing_' . time();

        if (!self::$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID ' .
                'environment variable');
        }

        if (!$creds = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
    }

    public function testCreateEntityType()
    {
        $response = $this->runCommand('entity-type-create', [
            'display-name' => self::$entityTypeDisplayName
        ]);
        $output = $this->runCommand('entity-type-list');

        $this->assertContains(self::$entityTypeDisplayName, $output);

        $response = str_replace(array("\r", "\n"), '', $response);
        $response = explode('/', $response);
        $entityTypeId = end($response);
        return $entityTypeId;
    }

    /** @depends testCreateEntityType */
    public function testDeleteEntityType($entityTypeId)
    {
        $this->runCommand('entity-type-delete', [
            'entity-type-id' => $entityTypeId
        ]);
        $output = $this->runCommand('entity-type-list');

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
