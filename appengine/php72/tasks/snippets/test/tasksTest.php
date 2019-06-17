<?php
/**
 * Copyright 2018 Google LLC.
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

namespace Google\Cloud\Samples\Appengine\Tasks\Tests;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for tasks commands.
 */
class TasksTest extends TestCase
{
    use TestTrait;

    public function testCreateTask()
    {
        $queue = $this->requireEnv('CLOUD_TASKS_APPENGINE_QUEUE');
        $location = $this->requireEnv('CLOUD_TASKS_LOCATION');

        $output = $this->runSnippet('create_task', [
            self::$projectId,
            $location,
            $queue,
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
}
