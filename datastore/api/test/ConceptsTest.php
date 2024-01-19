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
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;
use Google\Cloud\Datastore\Query\GqlQuery;
use Google\Cloud\Datastore\Query\Query;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
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
    use TestTrait;

    /* @var $hasCredentials boolean */
    protected static $hasCredentials;

    /* @var $keys array */
    protected static $keys = [];

    /* @var $datastore DatastoreClient */
    protected static $datastore;

    public static function setUpBeforeClass(): void
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp(): void
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
        $output = $this->runFunctionSnippet('basic_entity', [self::$datastore]);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testUpsert()
    {
        $output = $this->runFunctionSnippet('upsert', [
            self::$datastore
        ]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testInsert()
    {
        $output = $this->runFunctionSnippet('insert', [
            self::$datastore
        ]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testLookup()
    {
        $this->runFunctionSnippet('upsert', [self::$datastore]);

        $output = $this->runFunctionSnippet('lookup', [self::$datastore]);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testUpdate()
    {
        $output = $this->runFunctionSnippet('upsert', [self::$datastore]);
        $this->assertStringContainsString('[priority] => 4', $output);

        $output = $this->runFunctionSnippet('update', [self::$datastore]);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 5', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testDelete()
    {
        $taskKey = self::$datastore->key('Task', 'sampleTask');
        $output = $this->runFunctionSnippet('upsert', [self::$datastore]);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);

        $this->runFunctionSnippet('delete', [self::$datastore, $taskKey]);
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

        $this->runFunctionSnippet('batch_upsert', [
            self::$datastore, [$task1, $task2]
        ]);

        $output = $this->runFunctionSnippet('lookup', [self::$datastore, $key1]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => '.$path1, $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);

        $output = $this->runFunctionSnippet('lookup', [self::$datastore, $key2]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => '.$path2, $output);
        $this->assertStringContainsString('[category] => Work', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 0', $output);
        $this->assertStringContainsString('[description] => Finish writing sample', $output);
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

        $this->runFunctionSnippet('batch_upsert', [self::$datastore, [$task1, $task2]]);
        $output = $this->runFunctionSnippet('batch_lookup',[self::$datastore, [$key1, $key2]]);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => '.$path1, $output);
        $this->assertStringContainsString('[category] => '.$task1['category'], $output);
        $this->assertStringContainsString('[done] =>', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => '.$task1['description'], $output);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => '.$path2, $output);
        $this->assertStringContainsString('[category] => '.$task2['category'], $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 0', $output);
        $this->assertStringContainsString('[description] => '.$task2['description'], $output);
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

        $this->runFunctionSnippet('batch_upsert', [self::$datastore, [$task1, $task2]]);
        $this->runFunctionSnippet('batch_delete', [self::$datastore, [$key1, $key2]]);

        $output = $this->runFunctionSnippet('batch_lookup', [self::$datastore, [$key1, $key2]]);

        $this->assertStringContainsString('[missing] => ', $output);
        $this->assertStringNotContainsString('[found] => ', $output);
    }

    public function testNamedKey()
    {
        $output = $this->runFunctionSnippet('named_key', [self::$datastore]);
        $this->assertStringContainsString("Task", $output);
        $this->assertStringContainsString("sampleTask", $output);
    }

    public function testIncompleteKey()
    {
        $output = $this->runFunctionSnippet('incomplete_key', [self::$datastore]);
        $this->assertStringContainsString('Task', $output);
        $this->assertStringNotContainsString('name', $output);
        $this->assertStringNotContainsString('id', $output);
    }

    public function testKeyWithParent()
    {
        $output = $this->runFunctionSnippet('key_with_parent', [self::$datastore]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString("[name] => sampleTask", $output);
        $this->assertStringContainsString('[kind] => TaskList', $output);
        $this->assertStringContainsString("[name] => default", $output);
    }

    public function testKeyWithMultilevelParent()
    {
        $output = $this->runFunctionSnippet('key_with_multilevel_parent', [self::$datastore]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString("[name] => sampleTask", $output);
        $this->assertStringContainsString('[kind] => TaskList', $output);
        $this->assertStringContainsString("[name] => default", $output);
        $this->assertStringContainsString('[kind] => User', $output);
        $this->assertStringContainsString("[name] => alice", $output);
    }

    public function testProperties()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        $output = $this->runFunctionSnippet('properties', [self::$datastore, $key]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[created] => DateTime Object', $output);
        $this->assertStringContainsString('[date] => ', $output);
        $this->assertStringContainsString('[percent_complete] => 10', $output);
        $this->assertStringContainsString('[done] =>', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
    }

    // TODO:
    public function testArrayValue()
    {
        $key = self::$datastore->key('Task', generateRandomString());
        $output = $this->runFunctionSnippet('array_value', [self::$datastore, $key]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString("[name] => ", $output);
        $this->assertStringContainsString("[tags] => ", $output);
        $this->assertStringContainsString("[collaborators] => ", $output);
        $this->assertStringContainsString("fun", $output);
        $this->assertStringContainsString("programming", $output);
        $this->assertStringContainsString("alice", $output);
        $this->assertStringContainsString("bob", $output);

        // self::$keys[] = $key;
        // $task = array_value(self::$datastore, $key);
        // self::$datastore->upsert($task);
        // $task = self::$datastore->lookup($key);
        // $this->assertEquals(['fun', 'programming'], $task['tags']);
        // $this->assertEquals(['alice', 'bob'], $task['collaborators']);

        // $this->runEventuallyConsistentTest(function () use ($key) {
        //     $query = self::$datastore->query()
        //         ->kind('Task')
        //         ->projection(['tags', 'collaborators'])
        //         ->filter('collaborators', '<', 'charlie');
        //     $result = self::$datastore->runQuery($query);
        //     $this->assertInstanceOf(Iterator::class, $result);
        //     $num = 0;
        //     /* @var Entity $e */
        //     foreach ($result as $e) {
        //         $this->assertEquals($e->key()->path(), $key->path());
        //         $this->assertTrue(
        //             ($e['tags'] == 'fun')
        //             ||
        //             ($e['tags'] == 'programming')
        //         );
        //         $this->assertTrue(
        //             ($e['collaborators'] == 'alice')
        //             ||
        //             ($e['collaborators'] == 'bob')
        //         );
        //         $num += 1;
        //     }
        //     // The following 4 combinations should be in the result:
        //     // tags = 'fun', collaborators = 'alice'
        //     // tags = 'fun', collaborators = 'bob'
        //     // tags = 'programming', collaborators = 'alice'
        //     // tags = 'programming', collaborators = 'bob'
        //     self::assertEquals(4, $num);
        // });
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
        $output = $this->runFunctionSnippet('basic_query', [self::$datastore]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $output) {
                $this->assertStringContainsString('Found 2 records', $output);
                $this->assertStringContainsString($key1->path()[0]["name"], $output);
                $this->assertStringContainsString($key2->path()[0]["name"], $output);
            });
    }

    // TODO:
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

    // TODO:
    public function testRunGqlQuery()
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
        $query = basic_gql_query(self::$datastore);
        $this->assertInstanceOf(GqlQuery::class, $query);

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
        $output = $this->runFunctionSnippet('property_filter', [self::$datastore]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $output) {
                $this->assertStringContainsString('Found 1 records', $output);
                $this->assertStringContainsString($key1->path()[0]["name"], $output);
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
        $output = $this->runFunctionSnippet('composite_filter', [self::$datastore]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $output) {
                $this->assertStringContainsString('Found 1 records', $output);
                $this->assertStringContainsString($key1->path()[0]["name"], $output);
            });
    }

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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
        $output = $this->runFunctionSnippet('ancestor_query', [self::$datastore]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('Found Ancestors: 1', $output);
        $this->assertStringContainsString($uniqueValue, $output);
    }

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
    public function testInequalityInvalid()
    {
        $this->expectException('Google\Cloud\Core\Exception\BadRequestException');

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

    // TODO:
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

    // TODO:
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

    // TODO:
    public function testInequalitySortInvalidNotSame()
    {
        $this->expectException('Google\Cloud\Core\Exception\BadRequestException');

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

    // TODO:
    public function testInequalitySortInvalidNotFirst()
    {
        $this->expectException('Google\Cloud\Core\Exception\BadRequestException');

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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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
        $output = $this->runFunctionSnippet('entity_with_parent', [self::$datastore]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[kind] => TaskList', $output);
        $this->assertStringContainsString('[name] => default', $output);
    }

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    // TODO:
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

    public function tearDown(): void
    {
        if (! empty(self::$keys)) {
            self::$datastore->deleteBatch(self::$keys);
        }
    }
}
