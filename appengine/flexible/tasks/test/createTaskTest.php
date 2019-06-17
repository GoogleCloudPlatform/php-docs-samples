<?php
/**
 * Copyright 2017 Google Inc.
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
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for tasks commands.
 */
class tasksTest extends TestCase
{
    use TestTrait, ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../tasks.php';
    protected static $queue;
    protected static $location;

    public static function setUpBeforeClass()
    {
        self::$queue = self::requireEnv('CLOUD_TASKS_APPENGINE_QUEUE');
        self::$location = self::requireEnv('CLOUD_TASKS_LOCATION');
    }

    public function testCreateTask()
    {
        $output = $this->runCommand('create-task', [
            'project' => self::$projectId,
            'queue' => self::$queue,
            'location' => self::$location
        ]);
        $taskNamePrefix = sprintf('projects/%s/locations/%s/queues/%s/tasks/',
            self::$projectId,
            self::$location,
            self::$queue
        );
        $expectedOutput = sprintf('Created task %s', $taskNamePrefix);
        $this->assertContains($expectedOutput, $output);
    }
}
