<?php
/**
 * Copyright 2020 Google LLC.
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

namespace Google\Cloud\Samples\Tasks\Tests;

use Google\Auth\CredentialsLoader;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for tasks commands.
 */
class TasksTest extends TestCase
{
    use TestTrait;

    private static $queue;
    private static $location;

    public static function setUpBeforeClass(): void
    {
        self::$queue = self::requireEnv('CLOUD_TASKS_APPENGINE_QUEUE');
        self::$location = self::requireEnv('CLOUD_TASKS_LOCATION');
    }

    public function testCreateHttpTask()
    {
        $output = $this->runSnippet('create_http_task', [
            self::$location,
            self::$queue,
            'https://example.com/taskhandler',
            'Task Details',
        ]);

        $taskNamePrefix = $this->getTaskNamePrefix();
        $expectedOutput = sprintf('Created task %s', $taskNamePrefix);
        $this->assertStringContainsString($expectedOutput, $output);
    }

    public function testCreateHttpTaskWithToken()
    {
        $jsonKey = CredentialsLoader::fromEnv();
        $output = $this->runSnippet('create_http_task_with_token', [
            self::$location,
            self::$queue,
            'https://example.com/taskhandler',
            $jsonKey['client_email'],
            'Task Details',
        ]);

        $taskNamePrefix = $this->getTaskNamePrefix();
        $expectedOutput = sprintf('Created task %s', $taskNamePrefix);
        $this->assertStringContainsString($expectedOutput, $output);
    }

    private function getTaskNamePrefix()
    {
        $taskNamePrefix = sprintf('projects/%s/locations/%s/queues/%s/tasks/',
            self::$projectId,
            self::$location,
            self::$queue
        );
        return $taskNamePrefix;
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([0, self::$projectId], array_values($params));
        $argc = count($argv);
        ob_start();
        require __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }
}
