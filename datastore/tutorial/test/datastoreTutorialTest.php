<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\Samples\Datastore\Tasks;

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class datastoreTutorialTest extends TestCase
{
    use EventuallyConsistentTestTrait;
    use TestTrait;

    /** @var $retryCount int */
    protected static $retryCount = 5;

    private static $taskId;

    public function testAddTask()
    {
        $output = $this->runFunctionSnippet('add_task', [
            'projectId' => self::$projectId,
            'description' => 'buy milk',
        ]);
        $this->assertStringContainsString('Created new task with ID', $output);

        preg_match('/Created new task with ID (\d+)./', $output, $matches);
        self::$taskId = $matches[1];
    }

    /**
     * @depends testAddTask
     */
    public function testListTasks()
    {
        $expected = sprintf('ID: %d
  Description: buy milk
  Status: created', self::$taskId);
        $this->runEventuallyConsistentTest(function () use ($expected) {
            $output = $this->runFunctionSnippet('list_tasks', [self::$projectId]);
            $this->assertStringContainsString($expected, $output);
        }, self::$retryCount);
    }

    /**
     * @depends testListTasks
     */
    public function testMarkDone()
    {
        $output = $this->runFunctionSnippet('mark_done', [
            'projectId' => self::$projectId,
            'taskId' => self::$taskId,
        ]);
        $expected = sprintf('ID: %d
  Description: buy milk
  Status: done', self::$taskId);
        $this->runEventuallyConsistentTest(function () use ($expected) {
            $output = $this->runFunctionSnippet('list_tasks', [self::$projectId]);
            $this->assertStringContainsString($expected, $output);
        }, self::$retryCount);
    }

    /**
     * @depends testMarkDone
     */
    public function testDeleteTask()
    {
        $output = $this->runFunctionSnippet('delete_task', [
            self::$projectId,
            self::$taskId,
        ]);

        $this->assertStringContainsString('deleted successfully', $output);

        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('list_tasks', [self::$projectId]);
            $this->assertStringNotContainsString(self::$taskId, $output);
        });

        self::$taskId = null;
    }

    public static function tearDownAfterClass(): void
    {
        if (!empty(self::$taskId)) {
            $datastore = new DatastoreClient(['projectId' => self::$projectId]);
            $taskKey = $datastore->key('Task', self::$taskId);
            $datastore->delete($taskKey);
        }
    }
}
