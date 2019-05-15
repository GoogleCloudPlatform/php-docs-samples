<?php
/**
 * Copyright 2019 Google LLC.
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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for tasks commands.
 */
class TasksTest extends TestCase
{
    use TestTrait;

    public function testCreateHttpTask()
    {
        $queue = $this->requireEnv('CLOUD_TASKS_APPENGINE_QUEUE');
        $location = $this->requireEnv('CLOUD_TASKS_LOCATION');

        $output = $this->runSnippet('create_http_task', [
            $location,
            $queue,
            'http://example.com/taskhandler',
            'Task Details',
        ]);
        $taskNamePrefix = sprintf('projects/%s/locations/%s/queues/%s/tasks/',
            self::$projectId,
            $location,
            $queue
        );

        $expectedOutput = sprintf('Created task %s', $taskNamePrefix);
        $this->assertContains($expectedOutput, $output);
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
