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

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;
use Google\Cloud\Datastore\Query\Query;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class ConceptsTest extends TestCase
{
    use EventuallyConsistentTestTrait;
    use TestTrait;

    /* @var boolean $hasCredentials */
    protected static $hasCredentials;

    /* @var array $keys */
    protected static $keys = [];

    /* @var DatastoreClient $datastore */
    protected static $datastore;

    /* @var string $namespaceId */
    protected static string $namespaceId;

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
                . 'datastore emulator'
            );
        }
        self::$datastore = new DatastoreClient([
            'namespaceId' => self::$namespaceId = $this->generateRandomString()
        ]);
        self::$keys = [];
    }

    public function testBasicEntity()
    {
        $output = $this->runFunctionSnippet('basic_entity', [self::$namespaceId]);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testUpsert()
    {
        $output = $this->runFunctionSnippet('upsert', [self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testInsert()
    {
        $output = $this->runFunctionSnippet('insert', [self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testLookup()
    {
        $this->runFunctionSnippet('upsert', [self::$namespaceId]);

        $output = $this->runFunctionSnippet('lookup', ['sampleTask', self::$namespaceId]);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testUpdate()
    {
        $output = $this->runFunctionSnippet('upsert', [self::$namespaceId]);
        $this->assertStringContainsString('[priority] => 4', $output);

        $output = $this->runFunctionSnippet('update', [self::$namespaceId]);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 5', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);
    }

    public function testDelete()
    {
        $taskKeyId = 'sampleTask';
        $taskKey = self::$datastore->key('Task', $taskKeyId);
        $output = $this->runFunctionSnippet('upsert', [self::$namespaceId]);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);

        $this->runFunctionSnippet('delete', [$taskKeyId, self::$namespaceId]);
        $task = self::$datastore->lookup($taskKey);
        $this->assertNull($task);
    }

    public function testBatchUpsert()
    {
        $path1 = $this->generateRandomString();
        $path2 = $this->generateRandomString();
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

        $output = $this->runFunctionSnippet('batch_upsert', [
            [$task1, $task2],
            self::$namespaceId
        ]);
        $this->assertStringContainsString('Upserted 2 rows', $output);

        $output = $this->runFunctionSnippet('lookup', [$path1, self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => ' . $path1, $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => Learn Cloud Datastore', $output);

        $output = $this->runFunctionSnippet('lookup', [$path2, self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => ' . $path2, $output);
        $this->assertStringContainsString('[category] => Work', $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 0', $output);
        $this->assertStringContainsString('[description] => Finish writing sample', $output);
    }

    public function testBatchLookup()
    {
        $path1 = $this->generateRandomString();
        $path2 = $this->generateRandomString();
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

        $this->runFunctionSnippet('batch_upsert', [[$task1, $task2], self::$namespaceId]);
        $output = $this->runFunctionSnippet('batch_lookup', [[$path1, $path2], self::$namespaceId]);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => ' . $path1, $output);
        $this->assertStringContainsString('[category] => ' . $task1['category'], $output);
        $this->assertStringContainsString('[done] =>', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
        $this->assertStringContainsString('[description] => ' . $task1['description'], $output);

        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => ' . $path2, $output);
        $this->assertStringContainsString('[category] => ' . $task2['category'], $output);
        $this->assertStringContainsString('[done]', $output);
        $this->assertStringContainsString('[priority] => 0', $output);
        $this->assertStringContainsString('[description] => ' . $task2['description'], $output);
    }

    public function testBatchDelete()
    {
        $path1 = $this->generateRandomString();
        $path2 = $this->generateRandomString();
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

        $this->runFunctionSnippet('batch_upsert', [[$task1, $task2], self::$namespaceId]);
        $output = $this->runFunctionSnippet('batch_delete', [[$path1, $path2], self::$namespaceId]);
        $this->assertStringContainsString('Deleted 2 rows', $output);

        $output = $this->runFunctionSnippet('batch_lookup', [[$path1, $path2], self::$namespaceId]);

        $this->assertStringContainsString('[missing] => ', $output);
        $this->assertStringNotContainsString('[found] => ', $output);
    }

    public function testNamedKey()
    {
        $output = $this->runFunctionSnippet('named_key', [self::$namespaceId]);
        $this->assertStringContainsString('Task', $output);
        $this->assertStringContainsString('sampleTask', $output);
    }

    public function testIncompleteKey()
    {
        $output = $this->runFunctionSnippet('incomplete_key', [self::$namespaceId]);
        $this->assertStringContainsString('Task', $output);
        $this->assertStringNotContainsString('name', $output);
        $this->assertStringNotContainsString('id', $output);
    }

    public function testKeyWithParent()
    {
        $output = $this->runFunctionSnippet('key_with_parent', [self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[kind] => TaskList', $output);
        $this->assertStringContainsString('[name] => default', $output);
    }

    public function testKeyWithMultilevelParent()
    {
        $output = $this->runFunctionSnippet('key_with_multilevel_parent', [self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => sampleTask', $output);
        $this->assertStringContainsString('[kind] => TaskList', $output);
        $this->assertStringContainsString('[name] => default', $output);
        $this->assertStringContainsString('[kind] => User', $output);
        $this->assertStringContainsString('[name] => alice', $output);
    }

    public function testProperties()
    {
        $keyId = $this->generateRandomString();
        $output = $this->runFunctionSnippet('properties', [$keyId, self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[category] => Personal', $output);
        $this->assertStringContainsString('[created] => DateTime Object', $output);
        $this->assertStringContainsString('[date] => ', $output);
        $this->assertStringContainsString('[percent_complete] => 10', $output);
        $this->assertStringContainsString('[done] =>', $output);
        $this->assertStringContainsString('[priority] => 4', $output);
    }

    public function testArrayValue()
    {
        $keyId = $this->generateRandomString();
        $output = $this->runFunctionSnippet('array_value', [$keyId, self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[name] => ', $output);
        $this->assertStringContainsString('[tags] => Array', $output);
        $this->assertStringContainsString('[collaborators] => Array', $output);
        $this->assertStringContainsString('[0] => fun', $output);
        $this->assertStringContainsString('[1] => programming', $output);
        $this->assertStringContainsString('[0] => alice', $output);
        $this->assertStringContainsString('[1] => bob', $output);
    }

    public function testBasicQuery()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['priority'] = 4;
        $entity1['done'] = false;
        $entity2['priority'] = 5;
        $entity2['done'] = false;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $output = $this->runFunctionSnippet('basic_query', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $output) {
                $this->assertStringContainsString('Found 2 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
                $this->assertStringContainsString($key2->path()[0]['name'], $output);
            });
    }

    public function testRunQuery()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['priority'] = 4;
        $entity1['done'] = false;
        $entity2['priority'] = 5;
        $entity2['done'] = false;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $output = $this->runFunctionSnippet('basic_query', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $output) {
                $this->assertStringContainsString('Found 2 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
                $this->assertStringContainsString($key2->path()[0]['name'], $output);
            });
    }

    public function testRunGqlQuery()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['priority'] = 4;
        $entity1['done'] = false;
        $entity2['priority'] = 5;
        $entity2['done'] = false;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $output = $this->runFunctionSnippet('basic_gql_query', [self::$namespaceId]);
        $this->assertStringContainsString('Query\GqlQuery Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $output) {
                $this->assertStringContainsString('Found 2 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
                $this->assertStringContainsString($key2->path()[0]['name'], $output);
            });
    }

    public function testPropertyFilter()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['done'] = false;
        $entity2['done'] = true;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $output = $this->runFunctionSnippet('property_filter', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $output) {
                $this->assertStringContainsString('Found 1 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
            });
    }

    public function testCompositeFilter()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['done'] = false;
        $entity1['priority'] = 4;
        $entity2['done'] = false;
        $entity2['priority'] = 5;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $output = $this->runFunctionSnippet('composite_filter', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $output) {
                $this->assertStringContainsString('Found 1 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
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
        $output = $this->runFunctionSnippet('key_filter', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $output) {
                $this->assertStringContainsString('Found 1 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
            });
    }

    public function testAscendingSort()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['created'] = new \DateTime('2016-10-13 14:04:01');
        $entity2['created'] = new \DateTime('2016-10-13 14:04:00');
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $output = $this->runFunctionSnippet('ascending_sort', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $output) {
                $this->assertStringContainsString('Found 2 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
                $this->assertStringContainsString($key2->path()[0]['name'], $output);
            });
    }

    public function testDescendingSort()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['created'] = new \DateTime('2016-10-13 14:04:00');
        $entity2['created'] = new \DateTime('2016-10-13 14:04:01');
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $output = $this->runFunctionSnippet('descending_sort', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $output) {
                $this->assertStringContainsString('Found 2 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
                $this->assertStringContainsString($key2->path()[0]['name'], $output);
            });
    }

    public function testMultiSort()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
        $key3 = self::$datastore->key('Task', $this->generateRandomString());
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
        $output = $this->runFunctionSnippet('multi_sort', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $key3, $entity1, $entity2, $entity3, $output) {
                $this->assertStringContainsString('Found 3 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
                $this->assertStringContainsString($key2->path()[0]['name'], $output);
                $this->assertStringContainsString($key3->path()[0]['name'], $output);
                $this->assertStringContainsString($entity1['priority'], $output);
                $this->assertStringContainsString($entity2['priority'], $output);
                $this->assertStringContainsString($entity3['priority'], $output);
                $this->assertStringContainsString($entity1['created']->format('Y-m-d H:i:s'), $output);
                $this->assertStringContainsString($entity2['created']->format('Y-m-d H:i:s'), $output);
                $this->assertStringContainsString($entity3['created']->format('Y-m-d H:i:s'), $output);
            });
    }

    public function testAncestorQuery()
    {
        $key = self::$datastore->key('Task', $this->generateRandomString())
            ->ancestor('TaskList', 'default');
        $entity = self::$datastore->entity($key);
        $uniqueValue = $this->generateRandomString();
        $entity['prop'] = $uniqueValue;
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $output = $this->runFunctionSnippet('ancestor_query', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('Found Ancestors: 1', $output);
        $this->assertStringContainsString($uniqueValue, $output);
    }

    public function testKindlessQuery()
    {
        $key1 = self::$datastore->key('Task', 'taskWhichShouldMatch');
        $key2 = self::$datastore->key('Task', 'entityWhichShouldNotMatch');
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $lastSeenKeyId = 'lastSeen';
        $output = $this->runFunctionSnippet('kindless_query', [$lastSeenKeyId, self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);

        $this->runEventuallyConsistentTest(
            function () use ($key1, $key2, $output) {
                $this->assertStringContainsString('Found 1 records', $output);
                $this->assertStringContainsString($key1->path()[0]['name'], $output);
            });
    }

    public function testKeysOnlyQuery()
    {
        $key = self::$datastore->key('Task', $this->generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['prop'] = 'value';
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $this->runEventuallyConsistentTest(function () use ($key) {
            $output = $this->runFunctionSnippet('keys_only_query', [self::$namespaceId]);
            $this->assertStringContainsString('Query\Query Object', $output);
            $this->assertStringContainsString('Found keys: 1', $output);
            $this->assertStringContainsString($key->path()[0]['name'], $output);
        });
    }

    public function testProjectionQuery()
    {
        $key = self::$datastore->key('Task', $this->generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['prop'] = 'value';
        $entity['priority'] = 4;
        $entity['percent_complete'] = 50;
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('projection_query', [self::$namespaceId]);
            $this->assertStringContainsString('Query\Query Object', $output);
            $this->assertStringContainsString('Found keys: 1', $output);
            $this->assertStringContainsString('[priority] => 4', $output);
            $this->assertStringContainsString('[percent_complete] => 50', $output);
        });
    }

    public function testRunProjectionQuery()
    {
        $key = self::$datastore->key('Task', $this->generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['prop'] = 'value';
        $entity['priority'] = 4;
        $entity['percent_complete'] = 50;
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('run_projection_query', [null, self::$namespaceId]);
            $this->assertStringContainsString('[0] => 4', $output);
            $this->assertStringContainsString('[0] => 50', $output);
        });
    }

    public function testDistinctOn()
    {
        $key1 = self::$datastore->key('Task', $this->generateRandomString());
        $key2 = self::$datastore->key('Task', $this->generateRandomString());
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
            $output = $this->runFunctionSnippet('distinct_on', [self::$namespaceId]);
            $this->assertStringContainsString('Query\Query Object', $output);
            $this->assertStringContainsString('Found 1 records', $output);
            $this->assertStringContainsString('[priority] => 4', $output);
            $this->assertStringContainsString('[category] => work', $output);
            $this->assertStringContainsString($key1->path()[0]['name'], $output);
        });
    }

    public function testArrayValueFilters()
    {
        $key = self::$datastore->key('Task', $this->generateRandomString());
        $entity = self::$datastore->entity($key);
        $entity['tag'] = ['fun', 'programming'];
        self::$keys[] = $key;
        self::$datastore->upsert($entity);
        // This is a test for non-matching query for eventually consistent
        // query. This is hard, here we only sleep 5 seconds.
        sleep(5);
        $output = $this->runFunctionSnippet('array_value_inequality_range', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('No records found', $output);

        $this->runEventuallyConsistentTest(function () use ($key) {
            $output = $this->runFunctionSnippet('array_value_equality', [self::$namespaceId]);
            $this->assertStringContainsString('Found 1 records', $output);
            $this->assertStringContainsString('[kind] => Array', $output);
            $this->assertStringContainsString('[name] => Task', $output);
            $this->assertStringContainsString('[tag] => Array', $output);
            $this->assertStringContainsString('[0] => fun', $output);
            $this->assertStringContainsString('[1] => programming', $output);
            $this->assertStringContainsString($key->path()[0]['name'], $output);
        });
    }

    public function testLimit()
    {
        $entities = [];
        for ($i = 0; $i < 10; $i++) {
            $key = self::$datastore->key('Task', $this->generateRandomString());
            self::$keys[] = $key;
            $entities[] = self::$datastore->entity($key);
        }
        self::$datastore->upsertBatch($entities);
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('limit', [self::$namespaceId]);
            $this->assertStringContainsString('Query\Query Object', $output);
            $this->assertStringContainsString('Found 5 records', $output);
        });
    }

    // TODO:
    public function testCursorPaging()
    {
        $entities = [];
        for ($i = 0; $i < 5; $i++) {
            $key = self::$datastore->key('Task', $this->generateRandomString());
            self::$keys[] = $key;
            $entities[] = self::$datastore->entity($key);
        }
        self::$datastore->upsertBatch($entities);
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('cursor_paging', [3, '', self::$namespaceId]);
            $this->assertStringContainsString('Found 3 entities', $output);
            $this->assertStringContainsString('Found 2 entities with next page cursor', $output);
        });
    }

    public function testInequalityRange()
    {
        $output = $this->runFunctionSnippet('inequality_range', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('No records found', $output);
    }

    public function testEqualAndInequalityRange()
    {
        $output = $this->runFunctionSnippet('equal_and_inequality_range', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('No records found', $output);
    }

    public function testInequalitySort()
    {
        $output = $this->runFunctionSnippet('inequality_sort', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('No records found', $output);
    }

    public function testInequalitySortInvalidNotSame()
    {
        $this->expectException('Google\Cloud\Core\Exception\FailedPreconditionException');

        $output = $this->runFunctionSnippet('inequality_sort_invalid_not_same', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('No records found', $output);
        $this->assertStringContainsString('Google\Cloud\Core\Exception\BadRequestException', $output);
    }

    public function testInequalitySortInvalidNotFirst()
    {
        $this->expectException('Google\Cloud\Core\Exception\FailedPreconditionException');

        $output = $this->runFunctionSnippet('inequality_sort_invalid_not_first', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('No records found', $output);
        $this->assertStringContainsString('Google\Cloud\Core\Exception\BadRequestException', $output);
    }

    public function testUnindexedPropertyQuery()
    {
        $output = $this->runFunctionSnippet('unindexed_property_query', [self::$namespaceId]);
        $this->assertStringContainsString('Query\Query Object', $output);
        $this->assertStringContainsString('No records found', $output);
    }

    public function testExplodingProperties()
    {
        $output = $this->runFunctionSnippet('exploding_properties', [self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[tags] => Array', $output);
        $this->assertStringContainsString('[collaborators] => Array', $output);
        $this->assertStringContainsString('[created] => DateTime Object', $output);
        $this->assertStringContainsString('[0] => fun', $output);
        $this->assertStringContainsString('[1] => programming', $output);
        $this->assertStringContainsString('[2] => learn', $output);
        $this->assertStringContainsString('[0] => alice', $output);
        $this->assertStringContainsString('[1] => bob', $output);
        $this->assertStringContainsString('[2] => charlie', $output);
    }

    public function testTransferFunds()
    {
        $keyId1 = $this->generateRandomString();
        $keyId2 = $this->generateRandomString();
        $key1 = self::$datastore->key('Account', $keyId1);
        $key2 = self::$datastore->key('Account', $keyId2);
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['balance'] = 100;
        $entity2['balance'] = 0;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $this->runFunctionSnippet('transfer_funds', [$keyId1, $keyId2, 100, self::$namespaceId]);
        $fromAccount = self::$datastore->lookup($key1);
        $this->assertEquals(0, $fromAccount['balance']);
        $toAccount = self::$datastore->lookup($key2);
        $this->assertEquals(100, $toAccount['balance']);
    }

    public function testTransactionalRetry()
    {
        $keyId1 = $this->generateRandomString();
        $keyId2 = $this->generateRandomString();
        $key1 = self::$datastore->key('Account', $keyId1);
        $key2 = self::$datastore->key('Account', $keyId2);
        $entity1 = self::$datastore->entity($key1);
        $entity2 = self::$datastore->entity($key2);
        $entity1['balance'] = 10;
        $entity2['balance'] = 0;
        self::$keys = [$key1, $key2];
        self::$datastore->upsertBatch([$entity1, $entity2]);
        $this->runFunctionSnippet('transactional_retry', [$keyId1, $keyId2, self::$namespaceId]);
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
        $output = $this->runFunctionSnippet('get_task_list_entities', [self::$namespaceId]);
        $this->assertStringContainsString('Found 1 tasks', $output);
        $this->assertStringContainsString($taskKey->path()[0]['name'], $output);
        $this->assertStringContainsString('[description] => finish datastore sample', $output);
    }

    public function testEventualConsistentQuery()
    {
        $taskListKey = self::$datastore->key('TaskList', 'default');
        $taskKey = self::$datastore->key('Task', $this->generateRandomString())
            ->ancestorKey($taskListKey);
        $task = self::$datastore->entity(
            $taskKey,
            ['description' => 'learn eventual consistency']
        );
        self::$keys[] = $taskKey;
        self::$datastore->upsert($task);
        $this->runEventuallyConsistentTest(function () use ($taskKey) {
            $output = $this->runFunctionSnippet('get_task_list_entities', [self::$namespaceId]);
            $this->assertStringContainsString('Found 1 tasks', $output);
            $this->assertStringContainsString($taskKey->path()[0]['name'], $output);
            $this->assertStringContainsString('[description] => learn eventual consistency', $output);
        });
    }

    public function testEntityWithParent()
    {
        $output = $this->runFunctionSnippet('entity_with_parent', [self::$namespaceId]);
        $this->assertStringContainsString('[kind] => Task', $output);
        $this->assertStringContainsString('[kind] => TaskList', $output);
        $this->assertStringContainsString('[name] => default', $output);
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
                $output = $this->runFunctionSnippet('namespace_run_query', ['m', 'o', self::$namespaceId]);
                $this->assertStringContainsString('=> namespaceTest', $output);
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
            $output = $this->runFunctionSnippet('kind_run_query', [self::$namespaceId]);
            $this->assertStringContainsString('[0] => Account', $output);
            $this->assertStringContainsString('[1] => Task', $output);
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
            $output = $this->runFunctionSnippet('property_run_query', [self::$namespaceId]);
            $this->assertStringContainsString('[0] => Account.accountType', $output);
            $this->assertStringContainsString('[1] => Task.description', $output);
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
            $output = $this->runFunctionSnippet('property_by_kind_run_query', [self::$namespaceId]);
            $this->assertStringContainsString('[description] => Array', $output);
            $this->assertStringContainsString('[0] => STRING', $output);
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
            $output = $this->runFunctionSnippet('property_filtering_run_query', [self::$namespaceId]);
            $this->assertStringContainsString('[0] => Task.priority', $output);
            $this->assertStringContainsString('[1] => Task.tags', $output);
            $this->assertStringContainsString('[2] => TaskList.created', $output);
        });
    }

    public function tearDown(): void
    {
        if (! empty(self::$keys)) {
            self::$datastore->deleteBatch(self::$keys);
        }
    }

    /**
     * @param int $length Length of random string returned
     * @return string
     */
    private function generateRandomString($length = 10): string
    {
        // Character List to Pick from
        $chrList = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Minimum/Maximum times to repeat character List to seed from
        $repeatMin = 1; // Minimum times to repeat the seed string
        $repeatMax = 10; // Maximum times to repeat the seed string

        return substr(str_shuffle(str_repeat($chrList, mt_rand($repeatMin, $repeatMax))), 1, $length);
    }
}
