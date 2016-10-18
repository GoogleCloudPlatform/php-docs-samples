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

use Generator;
use Google;
// [START datastore_use]
use Google\Cloud\Datastore\DatastoreClient;
// [END datastore_use]
use Google\Cloud\Datastore\Entity;
use Google\Cloud\Datastore\Key;
use Google\Cloud\Datastore\Query\Query;

/**
 * Initialize the Datastore client.
 *
 * @return DatastoreClient
 */
function initialize_client()
{
    // [START initialize_client]
    $datastore = new DatastoreClient();
    // [END initialize_client]
    return $datastore;
}

/**
 * Create a Datastore entity.
 *
 * @param DatastoreClient $datastore
 * @return Google\Cloud\Datastore\Entity
 */
function basic_entity(DatastoreClient $datastore)
{
    // [START basic_entity]
    $task = $datastore->entity('Task', [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    // [END basic_entity]
    return $task;
}

/**
 * Create a Datastore entity and upsert it.
 *
 * @param DatastoreClient $datastore
 * @return Google\Cloud\Datastore\Entity
 */
function upsert(DatastoreClient $datastore)
{
    // [START upsert]
    $key = $datastore->key('Task', 'sampleTask');
    $task = $datastore->entity($key, [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    $datastore->upsert($task);
    // [END upsert]

    return $task;
}

/**
 * Create a Datastore entity and insert it. It will fail if there is already
 * an entity with the same key.
 *
 * @param DatastoreClient $datastore
 * @return Google\Cloud\Datastore\Entity
 */
function insert(DatastoreClient $datastore)
{
    // [START insert]
    $task = $datastore->entity('Task', [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    $datastore->insert($task);
    // [END insert]
    return $task;
}

/**
 * Look up a Datastore entity with the given key.
 *
 * @param DatastoreClient $datastore
 * @return Google\Cloud\Datastore\Entity|null
 */
function lookup(DatastoreClient $datastore)
{
    // [START lookup]
    $key = $datastore->key('Task', 'sampleTask');
    $task = $datastore->lookup($key);
    // [END lookup]
    return $task;
}

/**
 * Update a Datastore entity in a transaction.
 *
 * @param DatastoreClient $datastore
 * @return Google\Cloud\Datastore\Entity|null
 */
function update(DatastoreClient $datastore)
{
    // [START update]
    $transaction = $datastore->transaction();
    $key = $datastore->key('Task', 'sampleTask');
    $task = $transaction->lookup($key);
    $task['priority'] = 5;
    $transaction->upsert($task);
    $transaction->commit();
    // [END update]
    return $task;
}

/**
 * Delete a Datastore entity with the given key.
 *
 * @param DatastoreClient $datastore
 * @param Key $taskKey
 */
function delete(DatastoreClient $datastore, Key $taskKey)
{
    // [START delete]
    $datastore->delete($taskKey);
    // [END delete]
}

/**
 * Upsert multiple Datastore entities.
 *
 * @param DatastoreClient $datastore
 * @param array <Google\Cloud\Datastore\Entity> $tasks
 */
function batch_upsert(DatastoreClient $datastore, array $tasks)
{
    // [START batch_upsert]
    $datastore->upsertBatch($tasks);
    // [END batch_upsert]
}

/**
 * Lookup multiple entities.
 *
 * @param DatastoreClient $datastore
 * @param array <Key> $keys
 * @return array <Google\Cloud\Datastore\Entity>
 */
function batch_lookup(DatastoreClient $datastore, array $keys)
{
    // [START batch_lookup]
    $result = $datastore->lookupBatch($keys);
    if (isset($result['found'])) {
        // $result['found'] is an array of entities.
    } else {
        // No entities found.
    }
    // [END batch_lookup]
    return $result;
}

/**
 * Delete multiple Datastore entities with the given keys.
 *
 * @param DatastoreClient $datastore
 * @param array <Key> $keys
 */
function batch_delete(DatastoreClient $datastore, array $keys)
{
    // [START batch_delete]
    $datastore->deleteBatch($keys);
    // [END batch_delete]
}

/**
 * Create a complete Datastore key.
 *
 * @param DatastoreClient $datastore
 * @return Key
 */
function named_key(DatastoreClient $datastore)
{
    // [START named_key]
    $taskKey = $datastore->key('Task', 'sampleTask');
    // [END named_key]
    return $taskKey;
}

/**
 * Create an incomplete Datastore key.
 *
 * @param DatastoreClient $datastore
 * @return Key
 */
function incomplete_key(DatastoreClient $datastore)
{
    // [START incomplete_key]
    $taskKey = $datastore->key('Task');
    // [END incomplete_key]
    return $taskKey;
}

/**
 * Create a Datastore key with a parent with one level.
 *
 * @param DatastoreClient $datastore
 * @return Key
 */
function key_with_parent(DatastoreClient $datastore)
{
    // [START key_with_parent]
    $taskKey = $datastore->key('TaskList', 'default')
        ->pathElement('Task', 'sampleTask');
    // [END key_with_parent]
    return $taskKey;
}

/**
 * Create a Datastore key with a multi level parent.
 *
 * @param DatastoreClient $datastore
 * @return Key
 */
function key_with_multilevel_parent(DatastoreClient $datastore)
{
    // [START key_with_multilevel_parent]
    $taskKey = $datastore->key('User', 'alice')
        ->pathElement('TaskList', 'default')
        ->pathElement('Task', 'sampleTask');
    // [END key_with_multilevel_parent]
    return $taskKey;
}

/**
 * Create a Datastore entity, giving the excludeFromIndexes option.
 *
 * @param DatastoreClient $datastore
 * @param Key $key
 * @return Google\Cloud\Datastore\Entity
 */
function properties(DatastoreClient $datastore, Key $key)
{
    // [START properties]
    $task = $datastore->entity(
        $key,
        [
            'category' => 'Personal',
            'created' => new \DateTime(),
            'done' => false,
            'priority' => 4,
            'percent_complete' => 10.0,
            'description' => 'Learn Cloud Datastore'
        ],
        ['excludeFromIndexes' => ['description']]
    );
    // [END properties]
    return $task;
}

/**
 * Create a Datastore entity with some array properties.
 *
 * @param DatastoreClient $datastore
 * @param Key $key
 * @return Google\Cloud\Datastore\Entity
 */
function array_value(DatastoreClient $datastore, Key $key)
{
    // [START array_value]
    $task = $datastore->entity(
        $key,
        [
            'tags' => ['fun', 'programming'],
            'collaborators' => ['alice', 'bob']
        ]
    );
    // [END array_value]
    return $task;
}

/**
 * Create a basic Datastore query.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function basic_query(DatastoreClient $datastore)
{
    // [START basic_query]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('done', '=', false)
        ->filter('priority', '>=', 4)
        ->order('priority', Query::ORDER_DESCENDING);
    // [END basic_query]
    return $query;
}

/**
 * Run a given query.
 *
 * @param DatastoreClient $datastore
 * @return Generator <Google\Cloud\Datastore\Entity>
 */
function run_query(DatastoreClient $datastore, Query $query)
{
    // [START run_query]
    $result = $datastore->runQuery($query);
    // [END run_query]
    return $result;
}

/**
 * Create a query with a property filter.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function property_filter(DatastoreClient $datastore)
{
    // [START property_filter]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('done', '=', false);
    // [END property_filter]
    return $query;
}

/**
 * Create a query with a composite filter.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function composite_filter(DatastoreClient $datastore)
{
    // [START composite_filter]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('done', '=', false)
        ->filter('priority', '=', 4);
    // [END composite_filter]
    return $query;
}

/**
 * Create a query with a key filter.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function key_filter(DatastoreClient $datastore)
{
    // [START key_filter]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('__key__', '>', $datastore->key('Task', 'someTask'));
    // [END key_filter]
    return $query;
}

/**
 * Create a query with ascending sort.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function ascending_sort(DatastoreClient $datastore)
{
    // [START ascending_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->order('created');
    // [END ascending_sort]
    return $query;
}

/**
 * Create a query with descending sort.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function descending_sort(DatastoreClient $datastore)
{
    // [START descending_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->order('created', Query::ORDER_DESCENDING);
    // [END descending_sort]
    return $query;
}

/**
 * Create a query sorting with multiple properties.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function multi_sort(DatastoreClient $datastore)
{
    // [START multi_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->order('priority', Query::ORDER_DESCENDING)
        ->order('created');
    // [END multi_sort]
    return $query;
}

/**
 * Create an ancestor query.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function ancestor_query(DatastoreClient $datastore)
{
    // [START ancestor_query]
    $ancestorKey = $datastore->key('TaskList', 'default');
    $query = $datastore->query()
        ->kind('Task')
        ->hasAncestor($ancestorKey);
    // [END ancestor_query]
    return $query;
}

/**
 * Create a kindless query.
 *
 * @param DatastoreClient $datastore
 * @param Key $lastSeenKey
 * @return Query
 */
function kindless_query(DatastoreClient $datastore, Key $lastSeenKey)
{
    // [START kindless_query]
    $query = $datastore->query()
        ->filter('__key__', '>', $lastSeenKey);
    // [END kindless_query]
    return $query;
}

/**
 * Create a keys-only query.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function keys_only_query(DatastoreClient $datastore)
{
    // [START keys_only_query]
    $query = $datastore->query()
        ->keysOnly()
        ->limit(1);
    // [END keys_only_query]
    return $query;
}

/**
 * Create a projection query.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function projection_query(DatastoreClient $datastore)
{
    // [START projection_query]
    $query = $datastore->query()
        ->kind('Task')
        ->projection(['priority', 'percent_complete']);
    // [END projection_query]
    return $query;
}

/**
 * Run the given projection query and collect the projected properties.
 *
 * @param DatastoreClient $datastore
 * @param Query $query
 * @return array
 */
function run_projection_query(DatastoreClient $datastore, Query $query)
{
    // [START run_projection_query]
    $priorities = array();
    $percentCompletes = array();
    $result = $datastore->runQuery($query);
    /* @var Google\Cloud\Datastore\Entity $task */
    foreach ($result as $task) {
        $priorities[] = $task['priority'];
        $percentCompletes[] = $task['percent_complete'];
    }
    // [END run_projection_query]
    return array($priorities, $percentCompletes);
}

/**
 * Create a query with distinctOn.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function distinct_on(DatastoreClient $datastore)
{
    // [START distinct_on]
    $query = $datastore->query()
        ->kind('Task')
        ->order('category')
        ->order('priority')
        ->projection(['category', 'priority'])
        ->distinctOn('category');
    // [END distinct_on]
    return $query;
}

/**
 * Create a query with inequality filters.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function array_value_inequality_range(DatastoreClient $datastore)
{
    // [START array_value_inequality_range]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('tag', '>', 'learn')
        ->filter('tag', '<', 'math');
    // [END array_value_inequality_range]
    return $query;
}

/**
 * Create a query with equality filters.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function array_value_equality(DatastoreClient $datastore)
{
    // [START array_value_equality]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('tag', '=', 'fun')
        ->filter('tag', '=', 'programming');
    // [END array_value_equality]
    return $query;
}

/**
 * Create a query with a limit.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function limit(DatastoreClient $datastore)
{
    // [START limit]
    $query = $datastore->query()
        ->kind('Task')
        ->limit(5);
    // [END limit]
    return $query;
}

/**
 * Fetch a query cursor.
 *
 * @param DatastoreClient $datastore
 * @param string $pageSize
 * @param string $pageCursor
 * @return string $nextPageCursor
 */
function cursor_paging(DatastoreClient $datastore, $pageSize, $pageCursor = '')
{
    // [START cursor_paging]
    $query = $datastore->query()
        ->kind('Task')
        ->limit($pageSize)
        ->start($pageCursor);
    $result = $datastore->runQuery($query);
    $nextPageCursor = '';
    /* ignoreOnTheDocs */ $entities = [];
    /* @var Google\Cloud\Datastore\Entity $entity */
    foreach ($result as $entity) {
        $nextPageCursor = $entity->cursor();
        /* ignoreOnTheDocs */ $entities[] = $entity;
    }
    // [END cursor_paging]
    return array(
        'nextPageCursor' => $nextPageCursor,
        'entities' => $entities
    );
}

/**
 * Create a query with inequality range filters on the same property.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function inequality_range(DatastoreClient $datastore)
{
    // [START inequality_range]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('created', '>', new \DateTime('1990-01-01T00:00:00z'))
        ->filter('created', '<', new \DateTime('2000-12-31T23:59:59z'));
    // [END inequality_range]
    return $query;
}

/**
 * Create an invalid query with inequality filters on multiple properties.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function inequality_invalid(DatastoreClient $datastore)
{
    // [START inequality_invalid]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->filter('created', '>', new \DateTime('1990-01-01T00:00:00z'));
    // [END inequality_invalid]
    return $query;
}

/**
 * Create a query with equality filters and inequality range filters on a
 * single property.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function equal_and_inequality_range(DatastoreClient $datastore)
{
    // [START equal_and_inequality_range]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '=', 4)
        ->filter('done', '=', false)
        ->filter('created', '>', new \DateTime('1990-01-01T00:00:00z'))
        ->filter('created', '<', new \DateTime('2000-12-31T23:59:59z'));
    // [END equal_and_inequality_range]
    return $query;
}

/**
 * Create a query with an inequality filter and multiple sort orders.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function inequality_sort(DatastoreClient $datastore)
{
    // [START inequality_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->order('priority')
        ->order('created');
    // [END inequality_sort]
    return $query;
}

/**
 * Create an invalid query with an inequality filter and a wrong sort order.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function inequality_sort_invalid_not_same(DatastoreClient $datastore)
{
    // [START inequality_sort_invalid_not_same]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->order('created');
    // [END inequality_sort_invalid_not_same]
    return $query;
}

/**
 * Create an invalid query with an inequality filter and a wrong sort order.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function inequality_sort_invalid_not_first(DatastoreClient $datastore)
{
    // [START inequality_sort_invalid_not_first]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->order('created')
        ->order('priority');
    // [END inequality_sort_invalid_not_first]
    return $query;
}

/**
 * Create a query with an equality filter on 'description'.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function unindexed_property_query(DatastoreClient $datastore)
{
    // [START unindexed_property_query]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('description',  '=', 'A task description.');
    // [END unindexed_property_query]
    return $query;
}

/**
 * Create an entity with two array properties.
 *
 * @param DatastoreClient $datastore
 * @return Google\Cloud\Datastore\Entity
 */
function exploding_properties(DatastoreClient $datastore)
{
    // [START exploding_properties]
    $task = $datastore->entity(
        $datastore->key('Task'),
        [
            'tags' => ['fun', 'programming', 'learn'],
            'collaborators' => ['alice', 'bob', 'charlie'],
            'created' => new \DateTime(),
        ]
    );
    // [END exploding_properties]
    return $task;
}

// [START transactional_update]
/**
 * Update two entities in a transaction.
 *
 * @param DatastoreClient $datastore
 * @param Key $fromKey
 * @param Key $toKey
 * @param $amount
 */
function transfer_funds(
    DatastoreClient $datastore,
    Key $fromKey,
    Key $toKey,
    $amount
) {
    $transaction = $datastore->transaction();
    // The option 'sort' is important here, otherwise the order of the result
    // might be different from the order of the keys.
    $result = $transaction->lookupBatch([$fromKey, $toKey], ['sort' => true]);
    if (count($result['found']) != 2) {
        $transaction->rollback();
    }
    $fromAccount = $result['found'][0];
    $toAccount = $result['found'][1];
    $fromAccount['balance'] -= $amount;
    $toAccount['balance'] += $amount;
    $transaction->updateBatch([$fromAccount, $toAccount]);
    $transaction->commit();
}
// [END transactional_update]

/**
 * Call a function and retry upon conflicts for several times.
 *
 * @param DatastoreClient $datastore
 * @param Key $fromKey
 * @param Key $toKey
 */
function transactional_retry(
    DatastoreClient $datastore,
    Key $fromKey,
    Key $toKey
) {
    // [START transactional_retry]
    $retries = 5;
    for ($i = 0; $i < $retries; $i++) {
        try {
            transfer_funds($datastore, $fromKey, $toKey, 10);
        } catch (Google\Cloud\Exception\ConflictException $e) {
            // if $i >= $retries, the failure is final
            continue;
        }
        // Succeeded!
        break;
    }
    // [END transactional_retry]
}

/**
 * Insert an entity only if there is no entity with the same key.
 *
 * @param DatastoreClient $datastore
 * @param Entity $task
 */
function get_or_create(DatastoreClient $datastore, Entity $task)
{
    // [START transactional_get_or_create]
    $transaction = $datastore->transaction();
    $existed = $transaction->lookup($task->key());
    if ($existed === null) {
        $transaction->insert($task);
        $transaction->commit();
    }
    // [END transactional_get_or_create]
}

/**
 * Run a query with an ancestor inside a transaction.
 *
 * @param DatastoreClient $datastore
 * @return array<Entity>
 */
function get_task_list_entities(DatastoreClient $datastore)
{
    // [START transactional_single_entity_group_read_only]
    $transaction = $datastore->transaction();
    $taskListKey = $datastore->key('TaskList', 'default');
    $query = $datastore->query()
        ->kind('Task')
        ->filter('__key__', Query::OP_HAS_ANCESTOR, $taskListKey);
    $result = $transaction->runQuery($query);
    $taskListEntities = [];
    /* @var Entity $task */
    foreach ($result as $task) {
        $taskListEntities[] = $task;
    }
    $transaction->commit();
    // [END transactional_single_entity_group_read_only]
    return $taskListEntities;
}

/**
 * Create and run a query with readConsistency option.
 *
 * @param DatastoreClient $datastore
 * @return Generator
 */
function eventual_consistent_query(DatastoreClient $datastore)
{
    // [START eventual_consistent_query]
    $query = $datastore->query()
        ->kind('Task')
        ->hasAncestor($datastore->key('TaskList', 'default'));
    $result = $datastore->runQuery($query, ['readConsistency' => 'EVENTUAL']);
    // [END eventual_consistent_query]
    return $result;
}

/**
 * Create an entity with a parent key.
 *
 * @param DatastoreClient $datastore
 * @return Entity
 */
function entity_with_parent(DatastoreClient $datastore)
{
    // [START entity_with_parent]
    $parentKey = $datastore->key('TaskList', 'default');
    $key = $datastore->key('Task')->ancestorKey($parentKey);
    $task = $datastore->entity(
        $key,
        [
            'Category' => 'Personal',
            'Done' => false,
            'Priority' => 4,
            'Description' => 'Learn Cloud Datastore'
        ]
    );
    // [END entity_with_parent]
    return $task;
}

/**
 * Create and run a namespace query.
 *
 * @param DatastoreClient $datastore
 * @param string $start a starting namespace (inclusive)
 * @param string $end an ending namespace (exclusive)
 * @return array<string> namespaces returned from the query.
 */
function namespace_run_query(DatastoreClient $datastore, $start, $end)
{
    // [START namespace_run_query]
    $query = $datastore->query()
        ->kind('__namespace__')
        ->projection(['__key__'])
        ->filter('__key__', '>=', $datastore->key('__namespace__', $start))
        ->filter('__key__', '<', $datastore->key('__namespace__', $end));
    $result = $datastore->runQuery($query);
    /* @var array<string> $namespaces */
    $namespaces = [];
    foreach ($result as $namespace) {
        $namespaces[] = $namespace->key()->pathEnd()['name'];
    }
    // [END namespace_run_query]
    return $namespaces;
}

/**
 * Create and run a query to list all kinds in Datastore.
 *
 * @param DatastoreClient $datastore
 * @return array<string> kinds returned from the query
 */
function kind_run_query(DatastoreClient $datastore)
{
    // [START kind_run_query]
    $query = $datastore->query()
        ->kind('__kind__')
        ->projection(['__key__']);
    $result = $datastore->runQuery($query);
    /* @var array<string> $kinds */
    $kinds = [];
    foreach ($result as $kind) {
        $kinds[] = $kind->key()->pathEnd()['name'];
    }
    // [END kind_run_query]
    return $kinds;
}

/**
 * Create and run a property query.
 *
 * @param DatastoreClient $datastore
 * @return array<string>
 */
function property_run_query(DatastoreClient $datastore)
{
    // [START property_run_query]
    $query = $datastore->query()
        ->kind('__property__')
        ->projection(['__key__']);
    $result = $datastore->runQuery($query);
    /* @var array<string> $properties */
    $properties = [];
    /* @var Entity $entity */
    foreach ($result as $entity) {
        $kind = $entity->key()->path()[0]['name'];
        $propertyName = $entity->key()->path()[1]['name'];
        $properties[] = "$kind.$propertyName";
    }
    // [END property_run_query]
    return $properties;
}

/**
 * Create and run a property query with a kind.
 *
 * @param DatastoreClient $datastore
 * @return array<string => string>
 */
function property_by_kind_run_query(DatastoreClient $datastore)
{
    // [START property_by_kind_run_query]
    $ancestorKey = $datastore->key('__kind__', 'Task');
    $query = $datastore->query()
        ->kind('__property__')
        ->hasAncestor($ancestorKey);
    $result = $datastore->runQuery($query);
    /* @var array<string => string> $properties */
    $properties = [];
    /* @var Entity $entity */
    foreach ($result as $entity) {
        $propertyName = $entity->key()->path()[1]['name'];
        $propertyType = $entity['property_representation'];
        $properties[$propertyName] = $propertyType;
    }
    // Example values of $properties: ['description' => ['STRING']]
    // [END property_by_kind_run_query]
    return $properties;
}

/**
 * Create and run a property query with property filtering.
 *
 * @param DatastoreClient $datastore
 * @return array
 */
function property_filtering_run_query(DatastoreClient $datastore)
{
    // [START property_filtering_run_query]
    $ancestorKey = $datastore->key('__kind__', 'Task');
    $startKey = $datastore->key('__property__', 'priority')
        ->ancestorKey($ancestorKey);
    $query = $datastore->query()
        ->kind('__property__')
        ->filter('__key__', '>=', $startKey);
    $result = $datastore->runQuery($query);
    /* @var array<string> $properties */
    $properties = [];
    /* @var Entity $entity */
    foreach ($result as $entity) {
        $kind = $entity->key()->path()[0]['name'];
        $propertyName = $entity->key()->path()[1]['name'];
        $properties[] = "$kind.$propertyName";
    }
    // [END property_filtering_run_query]
    return $properties;
}
