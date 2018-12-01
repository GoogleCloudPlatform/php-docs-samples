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
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    use EventuallyConsistentTestTrait;

    /* @var $retryCount int */
    protected static $retryCount = 5;

    /* @var $hasCredentials boolean */
    protected static $hasCredentials;

    /* @var $keys array */
    protected static $keys = [];

    /* @var $datastore DatastoreClient */
    protected static $datastore;

    protected static $taskDesc;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        self::$datastore = build_datastore_service_with_namespace();
        self::$keys[] = self::$datastore->key('Task', 'sampleTask');
    }

    public function testBuildDatastoreService()
    {
        $client = build_datastore_service('my-project-id');
        $this->assertInstanceOf(DatastoreClient::class, $client);
    }

    public function testAddTask()
    {
        $task = add_task(self::$datastore, 'buy milk');
        self::$keys[] = $task->key();
        $this->assertEquals('buy milk', $task['description']);
        $this->assertInstanceOf(\DateTimeInterface::class, $task['created']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals('buy milk', $task['description']);
        $this->assertArrayHasKey('id', $task->key()->pathEnd());
    }

    public function testMarkDone()
    {
        $task = add_task(self::$datastore, 'buy milk');
        self::$keys[] = $task->key();
        mark_done(self::$datastore, $task->key()->pathEnd()['id']);
        $updated = self::$datastore->lookup($task->key());
        $this->assertEquals('buy milk', $updated['description']);
        $this->assertInstanceOf(\DateTimeInterface::class, $updated['created']);
        $this->assertEquals(true, $updated['done']);
        $this->assertEquals('buy milk', $updated['description']);
        $this->assertArrayHasKey('id', $updated->key()->pathEnd());
    }

    public function testDeleteTask()
    {
        $task = add_task(self::$datastore, 'buy milk');
        self::$keys[] = $task->key();
        delete_task(self::$datastore, $task->key()->pathEnd()['id']);
        $shouldBeNull = self::$datastore->lookup($task->key());
        $this->assertNull($shouldBeNull);
    }

    public function testListTasks()
    {
        $task = add_task(self::$datastore, 'buy milk');
        self::$keys[] = $task->key();
        $this->runEventuallyConsistentTest(function () {
            $result = list_tasks(self::$datastore);
            $found = 0;
            foreach ($result as $task) {
                if ($task['description'] === 'buy milk') {
                    $this->assertInstanceOf(
                        \DateTimeInterface::class,
                        $task['created']
                    );
                    $this->assertEquals(false, $task['done']);
                    $this->assertArrayHasKey('id', $task->key()->pathEnd());
                    $found += 1;
                }
            }
            $this->assertEquals(1, $found, 'It should list a new task.');
        }, self::$retryCount);
    }

    public function tearDown()
    {
        if (!empty(self::$keys)) {
            self::$datastore->deleteBatch(self::$keys);
        }
    }
}
