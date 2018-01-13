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

namespace Google\Cloud\Samples\Datastore;

use Iterator;
use Google;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;
use Google\Cloud\Datastore\Query\Query;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $ret = '';
    for ($i = 0; $i < $length; $i++) {
        $ret .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $ret;
}

class ConceptsTest extends TestCase
{
    use EventuallyConsistentTestTrait;

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
    }

    public function setUp()
    {
        $this->eventuallyConsistentRetryCount =
                getenv('DATASTORE_EVENTUALLY_CONSISTENT_RETRY_COUNT') ?: 3;
        if (!self::$hasCredentials &&
            getenv('DATASTORE_EMULATOR_HOST') === false) {
            $this->markTestSkipped(
                'No application credentials were found, also not using the '
                . 'datastore emulator');
        }
        self::$datastore = new DatastoreClient(
            array('namespaceId' => generateRandomString())
        );
        self::$keys = [];
    }

    public function testBasicEntity()
    {
        $task = basic_entity(self::$datastore);
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
    }

    public function testUpsert()
    {
        self::$keys[] = self::$datastore->key('Task', 'sampleTask');
        $task = upsert(self::$datastore);
        $task = self::$datastore->lookup($task->key());
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertEquals('sampleTask', $task->key()->pathEnd()['name']);
    }

    public function testInsert()
    {
        $task = insert(self::$datastore);
        self::$keys[] = $task->key();
        $task = self::$datastore->lookup($task->key());
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertArrayHasKey('id', $task->key()->pathEnd());
    }

    public function testLookup()
    {
        self::$keys[] = self::$datastore->key('Task', 'sampleTask');
        upsert(self::$datastore);
        $task = lookup(self::$datastore);
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertEquals('sampleTask', $task->key()->pathEnd()['name']);
    }

    public function testUpdate()
    {
        self::$keys[] = self::$datastore->key('Task', 'sampleTask');
        upsert(self::$datastore);
        update(self::$datastore);
        $task = lookup(self::$datastore);
        $this->assertEquals('Personal', $task['category']);
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(5, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
        $this->assertEquals('sampleTask', $task->key()->pathEnd()['name']);
    }

    public function testDelete()
    {
        $taskKey = self::$datastore->key('Task', generateRandomString());
        self::$keys[] = $taskKey;
        $task = self::$datastore->entity($taskKey);
        $task['category'] = 'Personal';
        $task['done'] = false;
        $task['priority'] = 4;
        $task['description'] = 'Learn Cloud Datastore';
        delete(self::$datastore, $taskKey);
        $task = self::$datastore->lookup($taskKey);
        $this->assertNull($task);
    }

    public function testBatchUpsert()
    {
        $path1 = generateRandomString();
        $path2 = generateRandomString();
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

        batch_upsert(self::$datastore, [$task1, $task2]);
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

    public function testBatchLookup()
    {
        $path1 = generateRandomString();
        $path2 = generateRandomString();
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

        batch_upsert(self::$datastore, [$task1, $task2]);
        $result = batch_lookup(self::$datastore, [$key1, $key2]);

        $this->assertArrayHasKey('found', $result);
        $tasks = $result['found'];

        $this->assertEquals(2, count($tasks));
        /* @var Entity $task */
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

    public function testBatchDelete()
    {
        $path1 = generateRandomString();
        $path2 = generateRandomString();
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

        batch_upsert(self::$datastore, [$task1, $task2]);
        batch_delete(self::$datastore, [$key1, $key2]);

        $result = batch_lookup(self::$datastore, [$key1, $key2]);
        $this->assertArrayNotHasKey('found', $result);
    }

    public function testNamedKey()
    {
        $key = named_key(self::$datastore);
        $this->assertEquals('Task', $key->pathEnd()['kind']);
        $this->assertEquals('sampleTask', $key->pathEnd()['name']);
    }

    public function testIncompleteKey()
    {
        $key = incomplete_key(self::$datastore);
        $this->assertEquals('Task', $key->pathEnd()['kind']);
        $this->assertArrayNotHasKey('name', $key->pathEnd());
        $this->assertArrayNotHasKey('id', $key->pathEnd());
    }

    public function testKeyWithParent()
    {
        $key = key_with_parent(self::$datastore);
        $this->assertEquals('Task', $key->path()[1]['kind']);
        $this->assertEquals('sampleTask', $key->path()[1]['name']);
        $this->assertEquals('TaskList', $key->path()[0]['kind']);
        $this->assertEquals('default', $key->path()[0]['name']);
    }

    public function testKeyWithMultilevelParent()
    {
        $key = key_with_multilevel_parent(self::$datastore);
        $this->assertEquals('Task', $key->path()[2]['kind']);
        $this->assertEquals('sampleTask', $key->path()[2]['name']);
        $this->assertEquals('TaskList', $key->path()[1]['kind']);
        $this->assertEquals('default', $key->path()[1]['name']);
        $this->assertEquals('User', $key->path()[0]['kind']);
        $this->assertEquals('alice', $key->path()[0]['name']);
    }

    public function testProperties()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        self::$keys[] = $key;
        $task = properties(self::$datastore, $key);
        self::$datastore->upsert($task);
        $task = self::$datastore->lookup($key);
        $this->assertEquals('Personal', $task['category']);
        $this->assertInstanceOf(\DateTimeInterface::class, $task['created']);
        $this->assertGreaterThanOrEqual($task['created'], new \DateTime());
        $this->assertEquals(false, $task['done']);
        $this->assertEquals(10.0, $task['percent_complete']);
        $this->assertEquals(4, $task['priority']);
        $this->assertEquals('Learn Cloud Datastore', $task['description']);
    }

    public function testArrayValue()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        self::$keys[] = $key;
        $task = array_value(self::$datastore, $key);
        self::$datastore->upsert($task);
        $task = self::$datastore->lookup($key);
        $this->assertEquals(['fun', 'programming'], $task['tags']);
        $this->assertEquals(['alice', 'bob'], $task['collaborators']);

        $this->runEventuallyConsistentTest(function () use ($key) {
            $query = self::$datastore->query()
                ->kind('Task')
                ->projection(['tags', 'collaborators'])
                ->filter('collaborators', '<', 'charlie');
            $result = self::$datastore->runQuery($query);
            $this->assertInstanceOf(Iterator::class, $result);
            $num = 0;
            /* @var Entity $e */
            foreach ($result as $e) {
                $this->assertEquals($e->key()->path(), $key->path());
                $this->assertTrue(
                    ($e['tags'] == 'fun')
                    ||
                    ($e['tags'] == 'programming')
                );
                $this->assertTrue(
                    ($e['collaborators'] == 'alice')
                    ||
                    ($e['collaborators'] == 'bob')
                );
                $num += 1;
            }
            // The following 4 combinations should be in the result:
            // tags = 'fun', collaborators = 'alice'
            // tags = 'fun', collaborators = 'bob'
            // tags = 'programming', collaborators = 'alice'
            // tags = 'programming', collaborators = 'bob'
            self::assertEquals(4, $num);
        });
    }

    public function testBasicQuery()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['priority'] = 4;
        $entity1['done'] = false;
        $entity2['priority'] = 5;
        $entity2['done'] = false;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $query = basic_query(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(2, $num);
                $this->assertTrue($entities[0]->key()->path() == $key2->path());
                $this->assertTrue($entities[1]->key()->path() == $key1->path());
            });
    }

    public function testRunQuery()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['priority'] = 4;
        $entity1['done'] = false;
        $entity2['priority'] = 5;
        $entity2['done'] = false;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $query = basic_query(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $query) {
                $result = run_query(self::$datastore, $query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(2, $num);
                $this->assertTrue($entities[0]->key()->path() == $key2->path());
                $this->assertTrue($entities[1]->key()->path() == $key1->path());
            });
    }

    public function testPropertyFilter()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['done'] = false;
        $entity2['done'] = true;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $query = property_filter(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(1, $num);
                $this->assertTrue($entities[0]->key()->path() == $key1->path());
            });
    }

    public function testCompositeFilter()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['done'] = false;
        $entity1['priority'] = 4;
        $entity2['done'] = false;
        $entity2['priority'] = 5;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $query = composite_filter(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(1, $num);
                $this->assertTrue($entities[0]->key()->path() == $key1->path());
            });
    }

    public function testKeyFilter()
    {
        $key1 = self::$datastore->key('Task', 'taskWhichShouldMatch');
        $key2 = self::$datastore->key('Task', 'keyWhichShouldNotMatch');
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $query = key_filter(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(1, $num);
                $this->assertTrue($entities[0]->key()->path() == $key1->path());
            });
    }

    public function testAscendingSort()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['created'] = new \DateTime('2016-10-13 14:04:01');
        $entity2['created'] = new \DateTime('2016-10-13 14:04:00');
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $query = ascending_sort(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(2, $num);
                $this->assertTrue($entities[0]->key()->path() == $key2->path());
                $this->assertTrue($entities[1]->key()->path() == $key1->path());
            });
    }

    public function testDescendingSort()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['created'] = new \DateTime('2016-10-13 14:04:00');
        $entity2['created'] = new \DateTime('2016-10-13 14:04:01');
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $query = descending_sort(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(2, $num);
                $this->assertTrue($entities[0]->key()->path() == $key2->path());
                $this->assertTrue($entities[1]->key()->path() == $key1->path());
            });
    }

    public function testMultiSort()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $key3 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity3 = self::$datastore->entity($key3);
        $entity3['created'] = new \DateTime('2016-10-13 14:04:03');
        $entity3['priority'] = 5;
        $entity2['created'] = new \DateTime('2016-10-13 14:04:01');
        $entity2['priority'] = 4;
        $entity1['created'] = new \DateTime('2016-10-13 14:04:02');
        $entity1['priority'] = 4;
        self::$keys = [$key1, $key2, $key3];
        self::$datastore->upsertBatch([$entity1, $entity2, $entity3]);
        $query = multi_sort(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $key3, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(3, $num);
                $this->assertTrue($entities[0]->key()->path() == $key3->path());
                $this->assertEquals(5, $entities[0]['priority']);
                $this->assertTrue($entities[1]->key()->path() == $key2->path());
                $this->assertEquals(4, $entities[1]['priority']);
                $this->assertTrue($entities[2]->key()->path() == $key1->path());
                $this->assertEquals(4, $entities[2]['priority']);
                $this->assertTrue($entities[0]['created'] > $entities[1]['created']);
                $this->assertTrue($entities[1]['created'] < $entities[2]['created']);
            });
    }

    public function testAncestorQuery()
    {
        $key = self::$datastore->key('Task', generateRandomString())
            ->ancestor('TaskList', 'default');
        $entity = self::$datastore->entity($key);
        $uniqueValue = generateRandomString();
        $entity['prop'] = $uniqueValue;
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $query = ancestor_query(self::$datastore);
        $this->assertInstanceOf(Query::class, $query);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        $found = false;
        foreach ($result as $e) {
            $found = true;
            self::assertEquals($uniqueValue, $e['prop']);
        }
        self::assertTrue($found);
    }

    public function testKindlessQuery()
    {
        $key1 = self::$datastore->key('Task', 'taskWhichShouldMatch');
        $key2 = self::$datastore->key('Task', 'entityWhichShouldNotMatch');
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $lastSeenKey = self::$datastore->key('Task', 'lastSeen');
        $query = kindless_query(self::$datastore, $lastSeenKey);
        $this->assertInstanceOf(Query::class, $query);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $query) {
                $result = self::$datastore->runQuery($query);
                $num = 0;
                $entities = [];
                /* @var Entity $e */
                foreach ($result as $e) {
                    $entities[] = $e;
                    $num += 1;
                }
                self::assertEquals(1, $num);
                $this->assertTrue($entities[0]->key()->path() == $key1->path());
            });
    }

    public function testKeysOnlyQuery()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['prop'] = 'value';
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $this->runEventuallyConsistentTest(function () use ($key) {
            $query = keys_only_query(self::$datastore);
            $result = self::$datastore->runQuery($query);
            $this->assertInstanceOf(Iterator::class, $result);
            $found = false;
            /* @var Entity $e */
            foreach ($result as $e) {
                $this->assertNull($e['prop']);
                $this->assertEquals($key->path(), $e->key()->path());
                $found = true;
                break;
            }
            self::assertTrue($found);
        });
    }

    public function testProjectionQuery()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['prop'] = 'value';
        $entity['priority'] = 4;
        $entity['percent_complete'] = 50;
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $this->runEventuallyConsistentTest(function () {
            $query = projection_query(self::$datastore);
            $result = self::$datastore->runQuery($query);
            $this->assertInstanceOf(Iterator::class, $result);
            $found = false;
            foreach ($result as $e) {
                $this->assertEquals(4, $e['priority']);
                $this->assertEquals(50, $e['percent_complete']);
                $this->assertNull($e['prop']);
                $found = true;
            }
            self::assertTrue($found);
        });
    }

    public function testRunProjectionQuery()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['prop'] = 'value';
        $entity['priority'] = 4;
        $entity['percent_complete'] = 50;
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $this->runEventuallyConsistentTest(function () {
            $query = projection_query(self::$datastore);
            $result = run_projection_query(self::$datastore, $query);
            $this->assertEquals(2, count($result));
            $this->assertEquals([4], $result[0]);
            $this->assertEquals([50], $result[1]);
        });
    }

    public function testDistinctOn()
    {
        $key1 = self::$datastore->key('Task', generateRandomString());
        $key2 = self::$datastore->key('Task', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['prop'] = 'value';
        $entity1['priority'] = 4;
        $entity1['category'] = 'work';
        $entity2['priority'] = 5;
        $entity2['category'] = 'work';
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $this->runEventuallyConsistentTest(function () use ($key1) {
            $query = distinct_on(self::$datastore);
            $result = self::$datastore->runQuery($query);
            $this->assertInstanceOf(Iterator::class, $result);
            $num = 0;
            /* @var Entity $e */
            foreach ($result as $e) {
                $this->assertEquals(4, $e['priority']);
                $this->assertEquals('work', $e['category']);
                $this->assertNull($e['prop']);
                $this->assertEquals($e->key()->path(), $key1->path());
                $num += 1;
            }
            self::assertEquals(1, $num);
        });
    }

    public function testArrayValueFilters()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['tag'] = ['fun', 'programming'];
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        // This is a test for non-matching query for eventually consistent
        // query. This is hard, here we only sleep 5 seconds.
        sleep(5);
        $query = array_value_inequality_range(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity. Here is the tag: %s',
                    var_export($e['tag'], true)
                )
            );
        }
        $this->runEventuallyConsistentTest(function () use ($key) {
            $query = array_value_equality(self::$datastore);
            $result = self::$datastore->runQuery($query);
            $this->assertInstanceOf(Iterator::class, $result);
            $num = 0;
            /* @var Entity $e */
            foreach ($result as $e) {
                $this->assertEquals(['fun', 'programming'], $e['tag']);
                $this->assertEquals($e->key()->path(), $key->path());
                $num += 1;
            }
            self::assertEquals(1, $num);
        });
    }

    public function testLimit()
    {
        $entities = [];
        for ($i = 0; $i < 10; $i++) {
            $key = self::$datastore->key('Task', generateRandomString());
            self::$keys[] = $key;
            $entities[] = self::$datastore->entity($key);
        }
        self::$datastore->upsertBatch($entities);
        $this->runEventuallyConsistentTest(function () {
            $query = limit(self::$datastore);
            $result = self::$datastore->runQuery($query);
            $this->assertInstanceOf(Iterator::class, $result);
            $num = 0;
            /* @var Entity $e */
            foreach ($result as $e) {
                $this->assertEquals('Task', $e->key()->path()[0]['kind']);
                $num += 1;
            }
            self::assertEquals(5, $num);
        });
    }

    public function testCursorPaging()
    {
        $entities = [];
        for ($i = 0; $i < 5; $i++) {
            $key = self::$datastore->key('Task', generateRandomString());
            self::$keys[] = $key;
            $entities[] = self::$datastore->entity($key);
        }
        self::$datastore->upsertBatch($entities);
        $this->runEventuallyConsistentTest(function () {
            $res = cursor_paging(self::$datastore, 3);
            $this->assertEquals(3, count($res['entities']));
            $res = cursor_paging(self::$datastore, 3, $res['nextPageCursor']);
            $this->assertEquals(2, count($res['entities']));
        });
    }

    public function testInequalityRange()
    {
        $query = inequality_range(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity with a key: %s',
                    var_export($e->key()->path(), true)
                )
            );
        }
    }

    /**
     * @expectedException Google\Cloud\Core\Exception\BadRequestException
     */
    public function testInequalityInvalid()
    {
        $query = inequality_invalid(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity with a key: %s',
                    var_export($e->key()->path(), true)
                )
            );
        }
    }

    public function testEqualAndInequalityRange()
    {
        $query = equal_and_inequality_range(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity with a key: %s',
                    var_export($e->key()->path(), true)
                )
            );
        }
    }

    public function testInequalitySort()
    {
        $query = inequality_sort(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity with a key: %s',
                    var_export($e->key()->path(), true)
                )
            );
        }
    }

    /**
     * @expectedException Google\Cloud\Core\Exception\BadRequestException
     */
    public function testInequalitySortInvalidNotSame()
    {
        $query = inequality_sort_invalid_not_same(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity with a key: %s',
                    var_export($e->key()->path(), true)
                )
            );
        }
    }

    /**
     * @expectedException Google\Cloud\Core\Exception\BadRequestException
     */
    public function testInequalitySortInvalidNotFirst()
    {
        $query = inequality_sort_invalid_not_first(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity with a key: %s',
                    var_export($e->key()->path(), true)
                )
            );
        }
    }

    public function testUnindexedPropertyQuery()
    {
        $query = unindexed_property_query(self::$datastore);
        $result = self::$datastore->runQuery($query);
        $this->assertInstanceOf(Iterator::class, $result);
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->fail(
                sprintf(
                    'Should not match the entity with this query with '
                    . ' a description: %s',
                    $e['description']
                )
            );
        }
    }

    public function testExplodingProperties()
    {
        $task = exploding_properties(self::$datastore);
        self::$datastore->insert($task);
        self::$keys[] = $task->key();
        $this->assertEquals(['fun', 'programming', 'learn'], $task['tags']);
        $this->assertEquals(
            ['alice', 'bob', 'charlie'],
            $task['collaborators']
        );
        $this->assertArrayHasKey('id', $task->key()->pathEnd());
    }

    public function testTransferFunds()
    {
        $key1 = self::$datastore->key('Account', generateRandomString());
        $key2 = self::$datastore->key('Account', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['balance'] = 100;
        $entity2['balance'] = 0;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        transfer_funds(self::$datastore, $key1, $key2, 100);
        $fromAccount = self::$datastore->lookup($key1);
        $this->assertEquals(0, $fromAccount['balance']);
        $toAccount = self::$datastore->lookup($key2);
        $this->assertEquals(100, $toAccount['balance']);
    }

    public function testTransactionalRetry()
    {
        $key1 = self::$datastore->key('Account', generateRandomString());
        $key2 = self::$datastore->key('Account', generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['balance'] = 10;
        $entity2['balance'] = 0;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        transactional_retry(self::$datastore, $key1, $key2);
        $fromAccount = self::$datastore->lookup($key1);
        $this->assertEquals(0, $fromAccount['balance']);
        $toAccount = self::$datastore->lookup($key2);
        $this->assertEquals(10, $toAccount['balance']);
    }

    public function testGetTaskListEntities()
    {
        $taskListKey = self::$datastore->key('TaskList', 'default');
        $taskKey = self::$datastore->key('Task', 'first task')
            ->ancestorKey($taskListKey);
        $task = self::$datastore->entity(
            $taskKey,
            ['description' => 'finish datastore sample']
        );
        self::$keys[] = $taskKey;
        self::$datastore->upsert($task);
        $result = get_task_list_entities(self::$datastore);
        $num = 0;
        /* @var Entity $e */
        foreach ($result as $e) {
            $this->assertEquals($taskKey->path(), $e->key()->path());
            $this->assertEquals('finish datastore sample', $e['description']);
            $num += 1;
        }
        self::assertEquals(1, $num);
    }

    public function testEventualConsistentQuery()
    {
        $taskListKey = self::$datastore->key('TaskList', 'default');
        $taskKey = self::$datastore->key('Task', generateRandomString())
            ->ancestorKey($taskListKey);
        $task = self::$datastore->entity(
            $taskKey,
            ['description' => 'learn eventual consistency']
        );
        self::$keys[] = $taskKey;
        self::$datastore->upsert($task);
        $this->runEventuallyConsistentTest(function () use ($taskKey) {
            $num = 0;
            $result = get_task_list_entities(self::$datastore);
            /* @var Entity $e */
            foreach ($result as $e) {
                $this->assertEquals($taskKey->path(), $e->key()->path());
                $this->assertEquals(
                    'learn eventual consistency',
                    $e['description']);
                $num += 1;
            }
            self::assertEquals(1, $num);
        });
    }

    public function testEntityWithParent()
    {
        $entity = entity_with_parent(self::$datastore);
        $parentPath = ['kind' => 'TaskList', 'name' => 'default'];
        $pathEnd = ['kind' => 'Task'];
        $this->assertEquals($parentPath, $entity->key()->path()[0]);
        $this->assertEquals($pathEnd, $entity->key()->path()[1]);
    }

    public function testNamespaceRunQuery()
    {
        $testNamespace = 'namespaceTest';
        $datastore = new DatastoreClient(
            ['namespaceId' => $testNamespace]
        );
        // Fixed namespace and the entity key. We don't need to clean it up.
        $key = $datastore->key('Task', 'namespaceTestKey');
        $datastore->upsert($datastore->entity($key));

        $this->runEventuallyConsistentTest(
            function () use ($datastore, $testNamespace) {
                $namespaces = namespace_run_query($datastore, 'm', 'o');
                $this->assertEquals([$testNamespace], $namespaces);
            }
        );
    }

    public function testKindRunQuery()
    {
        $key1 = self::$datastore->key('Account', 'alice');
        $key2 = self::$datastore->key('Task', 'Task1');
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $this->runEventuallyConsistentTest(function () {
            $kinds = kind_run_query(self::$datastore);
            $this->assertEquals(['Account', 'Task'], $kinds);
        });
    }

    public function testPropertyRunQuery()
    {
        $key1 = self::$datastore->key('Account', 'alice');
        $key2 = self::$datastore->key('Task', 'Task1');
        $entity1 = self::$datastore->entity($key1, ['accountType' => 'gold']);
        $entity2 = self::$datastore->entity($key2, ['description' => 'desc']);
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $this->runEventuallyConsistentTest(function () {
            $properties = property_run_query(self::$datastore);
            $this->assertEquals(
                ['Account.accountType', 'Task.description'],
                $properties
            );
        });
    }

    public function testPropertyByKindRunQuery()
    {
        $key1 = self::$datastore->key('Account', 'alice');
        $key2 = self::$datastore->key('Task', 'Task1');
        $entity1 = self::$datastore->entity($key1, ['accountType' => 'gold']);
        $entity2 = self::$datastore->entity($key2, ['description' => 'desc']);
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $this->runEventuallyConsistentTest(function () {
            $properties = property_by_kind_run_query(self::$datastore);
            $this->assertArrayHasKey('description', $properties);
            $this->assertEquals(['STRING'], $properties['description']);
        });
    }

    public function testPropertyFilteringRunQuery()
    {
        $key1 = self::$datastore->key('TaskList', 'default');
        $key2 = self::$datastore->key('Task', 'Task1');
        $entity1 = self::$datastore->entity(
            $key1,
            ['created' => new \Datetime()]
        );
        $entity2 = self::$datastore->entity(
            $key2,
            [
                'category' => 'work',
                'priority' => 4,
                'tags' => ['programming', 'fun']
            ]
        );
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $this->runEventuallyConsistentTest(function () {
            $properties = property_filtering_run_query(self::$datastore);
            $this->assertEquals(
                ['Task.priority', 'Task.tags', 'TaskList.created'],
                $properties
            );
        });
    }

    public function tearDown()
    {
        if (! empty(self::$keys)) {
            self::$datastore->deleteBatch(self::$keys);
        }
    }
}
