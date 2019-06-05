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

use DateTime;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;
use Google\Cloud\Datastore\EntityIterator;
use Google\Cloud\Datastore\Key;
use Google\Cloud\Datastore\Query\Query;

/**
 * Initialize the Datastore client.
 *
 * @return DatastoreClient
 */
function initialize_client()
{
    $datastore = new DatastoreClient();
    return $datastore;
}

/**
 * Create a Datastore entity.
 *
 * @param DatastoreClient $datastore
 * @return Entity
 */
function basic_entity(DatastoreClient $datastore)
{
    // [START datastore_basic_entity]
    $task = $datastore->entity('Task', [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    // [END datastore_basic_entity]
    return $task;
}

/**
 * Create a Datastore entity and upsert it.
 *
 * @param DatastoreClient $datastore
 * @return Entity
 */
function upsert(DatastoreClient $datastore)
{
    // [START datastore_upsert]
    $key = $datastore->key('Task', 'sampleTask');
    $task = $datastore->entity($key, [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    $datastore->upsert($task);
    // [END datastore_upsert]

    return $task;
}

/**
 * Create a Datastore entity and insert it. It will fail if there is already
 * an entity with the same key.
 *
 * @param DatastoreClient $datastore
 * @return Entity
 */
function insert(DatastoreClient $datastore)
{
    // [START datastore_insert]
    $task = $datastore->entity('Task', [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    $datastore->insert($task);
    // [END datastore_insert]
    return $task;
}

/**
 * Look up a Datastore entity with the given key.
 *
 * @param DatastoreClient $datastore
 * @return Entity|null
 */
function lookup(DatastoreClient $datastore)
{
    // [START datastore_lookup]
    $key = $datastore->key('Task', 'sampleTask');
    $task = $datastore->lookup($key);
    // [END datastore_lookup]
    return $task;
}

/**
 * Update a Datastore entity in a transaction.
 *
 * @param DatastoreClient $datastore
 * @return Entity|null
 */
function update(DatastoreClient $datastore)
{
    // [START datastore_update]
    $transaction = $datastore->transaction();
    $key = $datastore->key('Task', 'sampleTask');
    $task = $transaction->lookup($key);
    $task['priority'] = 5;
    $transaction->update($task);
    $transaction->commit();
    // [END datastore_update]
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
    // [START datastore_delete]
    $datastore->delete($taskKey);
    // [END datastore_delete]
}

/**
 * Upsert multiple Datastore entities.
 *
 * @param DatastoreClient $datastore
 * @param array <Entity> $tasks
 */
function batch_upsert(DatastoreClient $datastore, array $tasks)
{
    // [START datastore_batch_upsert]
    $datastore->upsertBatch($tasks);
    // [END datastore_batch_upsert]
}

/**
 * Lookup multiple entities.
 *
 * @param DatastoreClient $datastore
 * @param array <Key> $keys
 * @return array <Entity>
 */
function batch_lookup(DatastoreClient $datastore, array $keys)
{
    // [START datastore_batch_lookup]
    $result = $datastore->lookupBatch($keys);
    if (isset($result['found'])) {
        // $result['found'] is an array of entities.
    } else {
        // No entities found.
    }
    // [END datastore_batch_lookup]
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
    // [START datastore_batch_delete]
    $datastore->deleteBatch($keys);
    // [END datastore_batch_delete]
}

/**
 * Create a complete Datastore key.
 *
 * @param DatastoreClient $datastore
 * @return Key
 */
function named_key(DatastoreClient $datastore)
{
    // [START datastore_named_key]
    $taskKey = $datastore->key('Task', 'sampleTask');
    // [END datastore_named_key]
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
    // [START datastore_incomplete_key]
    $taskKey = $datastore->key('Task');
    // [END datastore_incomplete_key]
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
    // [START datastore_key_with_parent]
    $taskKey = $datastore->key('TaskList', 'default')
        ->pathElement('Task', 'sampleTask');
    // [END datastore_key_with_parent]
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
    // [START datastore_key_with_multilevel_parent]
    $taskKey = $datastore->key('User', 'alice')
        ->pathElement('TaskList', 'default')
        ->pathElement('Task', 'sampleTask');
    // [END datastore_key_with_multilevel_parent]
    return $taskKey;
}

/**
 * Create a Datastore entity, giving the excludeFromIndexes option.
 *
 * @param DatastoreClient $datastore
 * @param Key $key
 * @return Entity
 */
function properties(DatastoreClient $datastore, Key $key)
{
    // [START datastore_properties]
    $task = $datastore->entity(
        $key,
        [
            'category' => 'Personal',
            'created' => new DateTime(),
            'done' => false,
            'priority' => 4,
            'percent_complete' => 10.0,
            'description' => 'Learn Cloud Datastore'
        ],
        ['excludeFromIndexes' => ['description']]
    );
    // [END datastore_properties]
    return $task;
}

/**
 * Create a Datastore entity with some array properties.
 *
 * @param DatastoreClient $datastore
 * @param Key $key
 * @return Entity
 */
function array_value(DatastoreClient $datastore, Key $key)
{
    // [START datastore_array_value]
    $task = $datastore->entity(
        $key,
        [
            'tags' => ['fun', 'programming'],
            'collaborators' => ['alice', 'bob']
        ]
    );
    // [END datastore_array_value]
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
    // [START datastore_basic_query]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('done', '=', false)
        ->filter('priority', '>=', 4)
        ->order('priority', Query::ORDER_DESCENDING);
    // [END datastore_basic_query]
    return $query;
}

/**
 * Run a given query.
 *
 * @param DatastoreClient $datastore
 * @return EntityIterator<Entity>
 */
function run_query(DatastoreClient $datastore, Query $query)
{
    // [START datastore_run_query]
    $result = $datastore->runQuery($query);
    // [END datastore_run_query]
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
    // [START datastore_property_filter]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('done', '=', false);
    // [END datastore_property_filter]
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
    // [START datastore_composite_filter]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('done', '=', false)
        ->filter('priority', '=', 4);
    // [END datastore_composite_filter]
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
    // [START datastore_key_filter]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('__key__', '>', $datastore->key('Task', 'someTask'));
    // [END datastore_key_filter]
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
    // [START datastore_ascending_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->order('created');
    // [END datastore_ascending_sort]
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
    // [START datastore_descending_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->order('created', Query::ORDER_DESCENDING);
    // [END datastore_descending_sort]
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
    // [START datastore_multi_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->order('priority', Query::ORDER_DESCENDING)
        ->order('created');
    // [END datastore_multi_sort]
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
    // [START datastore_ancestor_query]
    $ancestorKey = $datastore->key('TaskList', 'default');
    $query = $datastore->query()
        ->kind('Task')
        ->hasAncestor($ancestorKey);
    // [END datastore_ancestor_query]
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
    // [START datastore_kindless_query]
    $query = $datastore->query()
        ->filter('__key__', '>', $lastSeenKey);
    // [END datastore_kindless_query]
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
    // [START datastore_keys_only_query]
    $query = $datastore->query()
        ->keysOnly();
    // [END datastore_keys_only_query]
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
    // [START datastore_projection_query]
    $query = $datastore->query()
        ->kind('Task')
        ->projection(['priority', 'percent_complete']);
    // [END datastore_projection_query]
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
    // [START datastore_run_query_projection]
    $priorities = array();
    $percentCompletes = array();
    $result = $datastore->runQuery($query);
    /* @var Entity $task */
    foreach ($result as $task) {
        $priorities[] = $task['priority'];
        $percentCompletes[] = $task['percent_complete'];
    }
    // [END datastore_run_query_projection]
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
    // [START datastore_distinct_on_query]
    $query = $datastore->query()
        ->kind('Task')
        ->order('category')
        ->order('priority')
        ->projection(['category', 'priority'])
        ->distinctOn('category');
    // [END datastore_distinct_on_query]
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
    // [START datastore_array_value_inequality_range]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('tag', '>', 'learn')
        ->filter('tag', '<', 'math');
    // [END datastore_array_value_inequality_range]
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
    // [START datastore_array_value_equality]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('tag', '=', 'fun')
        ->filter('tag', '=', 'programming');
    // [END datastore_array_value_equality]
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
    // [START datastore_limit]
    $query = $datastore->query()
        ->kind('Task')
        ->limit(5);
    // [END datastore_limit]
    return $query;
}

// [START datastore_cursor_paging]
/**
 * Fetch a query cursor.
 *
 * @param DatastoreClient $datastore
 * @param string $pageSize
 * @param string $pageCursor
 * @return array
 */
function cursor_paging(DatastoreClient $datastore, $pageSize, $pageCursor = '')
{
    $query = $datastore->query()
        ->kind('Task')
        ->limit($pageSize)
        ->start($pageCursor);
    $result = $datastore->runQuery($query);
    $nextPageCursor = '';
    $entities = [];
    /* @var Entity $entity */
    foreach ($result as $entity) {
        $nextPageCursor = $entity->cursor();
        $entities[] = $entity;
    }
    return array(
        'nextPageCursor' => $nextPageCursor,
        'entities' => $entities
    );
}
// [END datastore_cursor_paging]

/**
 * Create a query with inequality range filters on the same property.
 *
 * @param DatastoreClient $datastore
 * @return Query
 */
function inequality_range(DatastoreClient $datastore)
{
    // [START datastore_inequality_range]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('created', '>', new DateTime('1990-01-01T00:00:00z'))
        ->filter('created', '<', new DateTime('2000-12-31T23:59:59z'));
    // [END datastore_inequality_range]
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
    // [START datastore_inequality_invalid]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->filter('created', '>', new DateTime('1990-01-01T00:00:00z'));
    // [END datastore_inequality_invalid]
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
    // [START datastore_equal_and_inequality_range]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '=', 4)
        ->filter('done', '=', false)
        ->filter('created', '>', new DateTime('1990-01-01T00:00:00z'))
        ->filter('created', '<', new DateTime('2000-12-31T23:59:59z'));
    // [END datastore_equal_and_inequality_range]
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
    // [START datastore_inequality_sort]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->order('priority')
        ->order('created');
    // [END datastore_inequality_sort]
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
    // [START datastore_inequality_sort_invalid_not_same]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->order('created');
    // [END datastore_inequality_sort_invalid_not_same]
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
    // [START datastore_inequality_sort_invalid_not_first]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('priority', '>', 3)
        ->order('created')
        ->order('priority');
    // [END datastore_inequality_sort_invalid_not_first]
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
    // [START datastore_unindexed_property_query]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('description', '=', 'A task description.');
    // [END datastore_unindexed_property_query]
    return $query;
}

/**
 * Create an entity with two array properties.
 *
 * @param DatastoreClient $datastore
 * @return Entity
 */
function exploding_properties(DatastoreClient $datastore)
{
    // [START datastore_exploding_properties]
    $task = $datastore->entity(
        $datastore->key('Task'),
        [
            'tags' => ['fun', 'programming', 'learn'],
            'collaborators' => ['alice', 'bob', 'charlie'],
            'created' => new DateTime(),
        ]
    );
    // [END datastore_exploding_properties]
    return $task;
}

// [START datastore_transactional_update]
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
// [END datastore_transactional_update]

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
    // [START datastore_transactional_retry]
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
    // [END datastore_transactional_retry]
}

/**
 * Insert an entity only if there is no entity with the same key.
 *
 * @param DatastoreClient $datastore
 * @param Entity $task
 */
function get_or_create(DatastoreClient $datastore, Entity $task)
{
    // [START datastore_transactional_get_or_create]
    $transaction = $datastore->transaction();
    $existed = $transaction->lookup($task->key());
    if ($existed === null) {
        $transaction->insert($task);
        $transaction->commit();
    }
    // [END datastore_transactional_get_or_create]
}

/**
 * Run a query with an ancestor inside a transaction.
 *
 * @param DatastoreClient $datastore
 * @return array<Entity>
 */
function get_task_list_entities(DatastoreClient $datastore)
{
    // [START datastore_transactional_single_entity_group_read_only]
    $transaction = $datastore->readOnlyTransaction();
    $taskListKey = $datastore->key('TaskList', 'default');
    $query = $datastore->query()
        ->kind('Task')
        ->hasAncestor($taskListKey);
    $result = $transaction->runQuery($query);
    $taskListEntities = [];
    /* @var Entity $task */
    foreach ($result as $task) {
        $taskListEntities[] = $task;
    }
    // [END datastore_transactional_single_entity_group_read_only]
    return $taskListEntities;
}

/**
 * Create and run a query with readConsistency option.
 *
 * @param DatastoreClient $datastore
 * @return EntityIterator<Entity>
 */
function eventual_consistent_query(DatastoreClient $datastore)
{
    // [START datastore_eventual_consistent_query]
    $query = $datastore->query()
        ->kind('Task')
        ->hasAncestor($datastore->key('TaskList', 'default'));
    $result = $datastore->runQuery($query, ['readConsistency' => 'EVENTUAL']);
    // [END datastore_eventual_consistent_query]
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
    // [START datastore_entity_with_parent]
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
    // [END datastore_entity_with_parent]
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
    // [START datastore_namespace_run_query]
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
    // [END datastore_namespace_run_query]
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
    // [START datastore_kind_run_query]
    $query = $datastore->query()
        ->kind('__kind__')
        ->projection(['__key__']);
    $result = $datastore->runQuery($query);
    /* @var array<string> $kinds */
    $kinds = [];
    foreach ($result as $kind) {
        $kinds[] = $kind->key()->pathEnd()['name'];
    }
    // [END datastore_kind_run_query]
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
    // [START datastore_property_run_query]
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
    // [END datastore_property_run_query]
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
    // [START datastore_property_by_kind_run_query]
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
    // [END datastore_property_by_kind_run_query]
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
    // [START datastore_property_filtering_run_query]
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
    // [END datastore_property_filtering_run_query]
    return $properties;
}
