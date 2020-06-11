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

    private static $queue;
    private static $location;
    private static $url;
    private static $email;
    private static $payload;
    private static $haveVariablesBeenInitialized = false;

    public function testCreateHttpTask()
    {
        if(!self::$haveVariablesBeenInitialized){
            $this->initializeVariables();
            self::$haveVariablesBeenInitialized = true;
        }

        $output = $this->runSnippet('create_http_task', [
            self::$location,
            self::$queue,
            self::$url,
            self::$payload,
        ]);

        $expectedOutput = $this->getExpectedOutput();
        $this->assertContains($expectedOutput, $output);
    }

    public function testCreateHttpTaskWithToken()
    {
        if(!self::$haveVariablesBeenInitialized){
            $this->initializeVariables();
            self::$haveVariablesBeenInitialized = true;
        }

        $output = $this->runSnippet('create_http_task_with_token', [
            self::$location,
            self::$queue,
            self::$url,
            self::$email,
            self::$payload,
        ]);

        $expectedOutput = $this->getExpectedOutput();
        $this->assertContains($expectedOutput, $output);
    }

    private function initializeVariables(){
        self::$queue = $this->requireEnv('CLOUD_TASKS_APPENGINE_QUEUE');
        self::$location = $this->requireEnv('CLOUD_TASKS_LOCATION');
        self::$url = 'https://example.com/taskhandler';
        self::$email = 'php-docs-samples-testing@php-docs-samples-testing.iam.gserviceaccount.com';
        self::$payload = 'Task Details';
    }

    private function getExpectedOutput()
    {
        $taskNamePrefix = sprintf('projects/%s/locations/%s/queues/%s/tasks/',
            self::$projectId,
            self::$location,
            self::$queue
        );
        $expectedOutput = sprintf('Created task %s', $taskNamePrefix);
        return $expectedOutput;
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