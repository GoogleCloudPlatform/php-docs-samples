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
use Google\Cloud\Datastore\Key;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandSystemTest extends TestCase
{
    use EventuallyConsistentTestTrait;

    /* @var $keys array<Key> */
    private $keys;

    /* @var DatastoreClient $datastore */
    private $datastore;

    public function setUp()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        if (!($path && file_exists($path) && filesize($path) > 0)) {
            $this->markTestSkipped(
                'No service account credentials were found.'
            );
        }
        $this->datastore = build_datastore_service_with_namespace();
        // Also delete stale entities here.
        /* @var array<Key> $keys */
        $keys = [];
        $query = $this->datastore->query()->kind('Task');
        foreach ($this->datastore->runQuery($query) as $entity) {
            $keys[] = $entity->key();
        }
        $this->datastore->deleteBatch($keys);
        $this->keys = array();
    }

    public function tearDown()
    {
        if (!empty($this->keys)) {
            $this->datastore->deleteBatch($this->keys);
        }
    }

    public function testSeriesOfCommands()
    {
        $application = new Application();
        $application->add(new CreateTaskCommand());
        $application->add(new DeleteTaskCommand());
        $application->add(new ListTasksCommand());
        $application->add(new MarkTaskDoneCommand());

        // Test CreateTaskCommand
        $commandTester = new CommandTester($application->get('new'));
        $commandTester->execute(
            [
                'description' => 'run tests'
            ],
            ['interactive' => false]
        );
        $output = $commandTester->getDisplay();
        preg_match('/Created new task with ID (\d+)./', $output, $matches);
        $this->assertEquals(2, count($matches));
        $createdKey1 = $this->datastore->key('Task', intval($matches[1]));
        $this->keys[] = $createdKey1;

        // Create second task
        $commandTester->execute(
            [
                'description' => 'run tests twice'
            ],
            ['interactive' => false]
        );
        $output = $commandTester->getDisplay();
        preg_match('/Created new task with ID (\d+)./', $output, $matches);
        $this->assertEquals(2, count($matches));
        $createdKey2 = $this->datastore->key('Task', intval($matches[1]));
        $this->keys[] = $createdKey2;

        // Create third task
        $commandTester->execute(
            [
                'description' => 'run tests three times'
            ],
            ['interactive' => false]
        );
        $output = $commandTester->getDisplay();
        preg_match('/Created new task with ID (\d+)./', $output, $matches);
        $this->assertEquals(2, count($matches));
        $createdKey3 = $this->datastore->key('Task', intval($matches[1]));
        $this->keys[] = $createdKey3;

        // First confirm the existence
        $firstTask = $this->datastore->lookup($createdKey1);
        $this->assertNotNull($firstTask);
        $this->assertEquals(false, $firstTask['done']);

        // Test MarkTaskDoneCommand
        $commandTester = new CommandTester($application->get('done'));
        $commandTester->execute(
            [
                'taskId' => $createdKey1->pathEnd()['id']
            ],
            ['interactive' => false]
        );
        $output = $commandTester->getDisplay();
        preg_match('/Task (\d+) updated successfully./', $output, $matches);
        $this->assertEquals(2, count($matches));
        $this->assertEquals($createdKey1->pathEnd()['id'], intval($matches[1]));

        // Confirm it's marked as done.
        $firstTask = $this->datastore->lookup($createdKey1);
        $this->assertNotNull($firstTask);
        $this->assertEquals(true, $firstTask['done']);

        // Test DeleteTaskCommand
        $commandTester = new CommandTester($application->get('delete'));
        $commandTester->execute(
            [
                'taskId' => $createdKey1->pathEnd()['id']
            ],
            ['interactive' => false]
        );
        $output = $commandTester->getDisplay();
        preg_match('/Task (\d+) deleted successfully./', $output, $matches);
        $this->assertEquals(2, count($matches));
        $this->assertEquals($createdKey1->pathEnd()['id'], intval($matches[1]));

        // Confirm it's gone.
        $firstTask = $this->datastore->lookup($createdKey1);
        $this->assertNull($firstTask);

        // Test ListTasksCommand
        $commandTester = new CommandTester($application->get('list-tasks'));
        $this->runEventuallyConsistentTest(function () use ($commandTester) {
            $commandTester->execute([], ['interactive' => false]);
            $output = $commandTester->getDisplay();
            $this->assertRegExp('/run tests twice/', $output);
            $this->assertRegExp('/run tests three times/', $output);
        });
    }
}
