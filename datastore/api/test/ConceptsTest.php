<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Datastore;

use Google\Cloud\Datastore\DatastoreClient;

class ConceptsTest extends \PHPUnit_Framework_TestCase
{
    /* @var $hasCredentials boolean */
    protected static $hasCredentials;

    /* @var $keys array */
    protected static $keys = [];

    /* @var $datastore DatastoreClient */
    protected static $datastore;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        self::$datastore = new DatastoreClient(
            array('namespaceId' => uniqid())
        );
    }

    public function setUp()
    {
        if (!self::$hasCredentials &&
            getenv('DATASTORE_EMULATOR_HOST') === false) {
            $this->markTestSkipped(
                'No application credentials were found, also not using the '
                . 'datastore emulator');
        }
    }

    public function testCreateEntity()
    {
        $task = create_entity(self::$datastore);
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
    }

    public function testUpsertEntity()
    {
        $task = upsert_entity(self::$datastore);
        $task = self::$datastore->lookup($task->key());
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertEquals('sampleTask', $task->key()->pathEnd()['name']);
        self::$keys[] = $task->key();
    }

    public function testInsertEntity()
    {
        $task = insert_entity(self::$datastore);
        $task = self::$datastore->lookup($task->key());
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertArrayHasKey('id', $task->key()->pathEnd());
        self::$keys[] = $task->key();
    }

    public function testLookup()
    {
        upsert_entity(self::$datastore);
        $task = lookup(self::$datastore);
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertEquals('sampleTask', $task->key()->pathEnd()['name']);
    }

    public function testUpdate()
    {
        upsert_entity(self::$datastore);
        update_entity(self::$datastore);
        $task = lookup(self::$datastore);
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(5, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertEquals('sampleTask', $task->key()->pathEnd()['name']);
    }

    public function testDelete()
    {
        $taskKey = self::$datastore->key('Task', uniqid());
        $task = self::$datastore->entity($taskKey);
        $task['category'] = 'Personal';
        $task['done'] = false;
        $task['priority'] = 4;
        $task['description'] = 'Learn Cloud Datastore';
        delete_entity(self::$datastore, $taskKey);
        $task = self::$datastore->lookup($taskKey);
        $this->assertNull($task);
    }

    public function testUpsertMulti()
    {
        $path1 = uniqid();
        $path2 = uniqid();
        $key1 = self::$datastore->key('Task', $path1);
        $key2 = self::$datastore->key('Task', $path2);
        $task1 = self::$datastore->entity($key1);
        $task1['category'] = 'Personal';
        $task1['done'] = false;
        $task1['priority'] = 4;
        $task1['description'] = 'Learn Cloud Datastore';
        $task2 = self::$datastore->entity($key2);
        $task2['category'] = 'Work';
        $task2['done'] = true;
        $task2['priority'] = 0;
        $task2['description'] = 'Finish writing sample';
        self::$keys[] = $key1;
        self::$keys[] = $key2;

        upsert_multi(self::$datastore, [$task1, $task2]);
        $task1 = self::$datastore->lookup($key1);
        $task2 = self::$datastore->lookup($key2);

        $this->assertEquals('Personal', $task1['category']);
        $this->assertEquals(false, $task1['done']);
        $this->assertEquals(4, $task1['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task1['description']);
        $this->assertEquals($path1, $task1->key()->pathEnd()['name']);

        $this->assertEquals('Work', $task2['category']);
        $this->assertEquals(true, $task2['done']);
        $this->assertEquals(0, $task2['priority']);
        $this->assertEquals('Finish writing sample', $task2['description']);
        $this->assertEquals($path2, $task2->key()->pathEnd()['name']);
    }

    public function testLookupMulti()
    {
        $path1 = uniqid();
        $path2 = uniqid();
        $key1 = self::$datastore->key('Task', $path1);
        $key2 = self::$datastore->key('Task', $path2);
        $task1 = self::$datastore->entity($key1);
        $task1['category'] = 'Personal';
        $task1['done'] = false;
        $task1['priority'] = 4;
        $task1['description'] = 'Learn Cloud Datastore';
        $task2 = self::$datastore->entity($key2);
        $task2['category'] = 'Work';
        $task2['done'] = true;
        $task2['priority'] = 0;
        $task2['description'] = 'Finish writing sample';
        self::$keys[] = $key1;
        self::$keys[] = $key2;

        upsert_multi(self::$datastore, [$task1, $task2]);
        $result = lookup_multi(self::$datastore, [$key1, $key2]);

        $this->assertArrayHasKey('found', $result);
        $tasks = $result['found'];

        $this->assertEquals(2, count($tasks));
        foreach ($tasks as $task) {
            if ($task->key()->pathEnd()['name'] === $path1) {
                $task1 = $task;
            } elseif ($task->key()->pathEnd()['name'] === $path2) {
                $task2 = $task;
            } else {
                $this->fail(
                    sprintf(
                        'Got an unexpected entity with the path:%s',
                        $task->key()->pathEnd()['name']
                    )
                );
            }
        }
        $this->assertEquals('Personal', $task1['category']);
        $this->assertEquals(false, $task1['done']);
        $this->assertEquals(4, $task1['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task1['description']);
        $this->assertEquals($path1, $task1->key()->pathEnd()['name']);

        $this->assertEquals('Work', $task2['category']);
        $this->assertEquals(true, $task2['done']);
        $this->assertEquals(0, $task2['priority']);
        $this->assertEquals('Finish writing sample', $task2['description']);
        $this->assertEquals($path2, $task2->key()->pathEnd()['name']);
    }

    public function testDeleteMulti()
    {
        $path1 = uniqid();
        $path2 = uniqid();
        $key1 = self::$datastore->key('Task', $path1);
        $key2 = self::$datastore->key('Task', $path2);
        $task1 = self::$datastore->entity($key1);
        $task1['category'] = 'Personal';
        $task1['done'] = false;
        $task1['priority'] = 4;
        $task1['description'] = 'Learn Cloud Datastore';
        $task2 = self::$datastore->entity($key2);
        $task2['category'] = 'Work';
        $task2['done'] = true;
        $task2['priority'] = 0;
        $task2['description'] = 'Finish writing sample';
        self::$keys[] = $key1;
        self::$keys[] = $key2;

        upsert_multi(self::$datastore, [$task1, $task2]);
        delete_multi(self::$datastore, [$key1, $key2]);

        $result = lookup_multi(self::$datastore, [$key1, $key2]);
        $this->assertArrayNotHasKey('found', $result);
    }

    public function testCreateCompleteKey()
    {
        $key = create_complete_key(self::$datastore);
        $this->assertEquals('Task', $key->pathEnd()['kind']);
        $this->assertEquals('sampleTask', $key->pathEnd()['name']);
    }

    public function testCreateIncompleteKey()
    {
        $key = create_incomplete_key(self::$datastore);
        $this->assertEquals('Task', $key->pathEnd()['kind']);
        $this->assertArrayNotHasKey('name', $key->pathEnd());
        $this->assertArrayNotHasKey('id', $key->pathEnd());
    }

    public function testCreateKeyWithParent()
    {
        $key = create_key_with_parent(self::$datastore);
        $this->assertEquals('Task', $key->path()[1]['kind']);
        $this->assertEquals('sampleTask', $key->path()[1]['name']);
        $this->assertEquals('TaskList', $key->path()[0]['kind']);
        $this->assertEquals('default', $key->path()[0]['name']);
    }

    public function testCreateKeyWithMultiLevelParent()
    {
        $key = create_key_with_multi_level_parent(self::$datastore);
        $this->assertEquals('Task', $key->path()[2]['kind']);
        $this->assertEquals('sampleTask', $key->path()[2]['name']);
        $this->assertEquals('TaskList', $key->path()[1]['kind']);
        $this->assertEquals('default', $key->path()[1]['name']);
        $this->assertEquals('User', $key->path()[0]['kind']);
        $this->assertEquals('alice', $key->path()[0]['name']);
    }

    public function testCreateEntityWithOption()
    {
        $key = self::$datastore->key('Task', uniqid());
        self::$keys[] = $key;
        $task = create_entity_with_option(self::$datastore, $key);
        $now = new \DateTime();
        self::$datastore->upsert($task);
        $task = self::$datastore->lookup($key);
        $this->assertEquals('Personal', $task['category']);
        $this->assertInstanceOf(\DateTimeInterface::class, $task['created']);
        $this->assertGreaterThanOrEqual($now, $task['created']);
        $this->assertGreaterThanOrEqual($task['created'], new \DateTime());
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(10.0, $task['percent_complete']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
    }

    public function testCreateEntityWithArrayProperty()
    {
        $key = self::$datastore->key('Task', uniqid());
        self::$keys[] = $key;
        $task = create_entity_with_array_property(self::$datastore, $key);
        self::$datastore->upsert($task);
        $task = self::$datastore->lookup($key);
        $this->assertEquals(['fun', 'programming'], $task['tags']);
        $this->assertEquals(['alice', 'bob'], $task['collaborators']);
    }

    public static function tearDownAfterClass()
    {
        self::$datastore->deleteBatch(self::$keys);
    }
}
