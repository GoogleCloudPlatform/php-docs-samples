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

// [START datastore_use ]
use Google\Cloud\Datastore\DatastoreClient;

// [END datastore_use ]
use Google\Cloud\Datastore\Key;

/**
 * Initialize the Datastore client.
 *
 * @return DatastoreClient
 */
function initialize_client()
{
    // [START init_client]
    $datastore = new DatastoreClient();
    // [END init_client]
    return $datastore;
}

/**
 * Create a Datastore entity.
 *
 * @param DatastoreClient $datastore
 * @return \Google\Cloud\Datastore\Entity
 */
function create_entity(DatastoreClient $datastore)
{
    // [START create_entity]
    $task = $datastore->entity('Task', [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    // [END create_entity]
    return $task;
}

/**
 * Create a Datastore entity and upsert it.
 *
 * @param DatastoreClient $datastore
 * @return \Google\Cloud\Datastore\Entity
 */
function upsert_entity(DatastoreClient $datastore)
{
    // [START upsert_entity]
    $key = $datastore->key('Task', 'sampleTask');
    $task = $datastore->entity($key, [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    $datastore->upsert($task);
    // [END upsert_entity]

    return $task;
}

/**
 * Create a Datastore entity and insert it. It will fail if there is already
 * an entity with the same key.
 *
 * @param DatastoreClient $datastore
 * @return \Google\Cloud\Datastore\Entity
 */
function insert_entity(DatastoreClient $datastore)
{
    // [START insert_entity]
    $task = $datastore->entity('Task', [
        'category' => 'Personal',
        'done' => false,
        'priority' => 4,
        'description' => 'Learn Cloud Datastore'
    ]);
    $datastore->insert($task);
    // [END insert_entity]
    return $task;
}

/**
 * Look up a Datastore entity with the given key.
 *
 * @param DatastoreClient $datastore
 * @return \Google\Cloud\Datastore\Entity|null
 */
function lookup(DatastoreClient $datastore)
{
    // [START lookup_entity]
    $key = $datastore->key('Task', 'sampleTask');
    $task = $datastore->lookup($key);
    // [END lookup_entity]
    return $task;
}

/**
 * Update a Datastore entity in a transaction.
 *
 * @param DatastoreClient $datastore
 * @return \Google\Cloud\Datastore\Entity|null
 */
function update_entity(DatastoreClient $datastore)
{
    // [START update_entity]
    $transaction = $datastore->transaction();
    $key = $datastore->key('Task', 'sampleTask');
    $task = $transaction->lookup($key);
    $task['priority'] = 5;
    $transaction->upsert($task);
    $transaction->commit();
    // [END update_entity]
    return $task;
}

/**
 * Delete a Datastore entity with the given key.
 *
 * @param DatastoreClient $datastore
 * @param Key $taskKey
 */
function delete_entity(DatastoreClient $datastore, Key $taskKey)
{
    // [START delete_entity]
    $datastore->delete($taskKey);
    // [END delete_entity]
}

/**
 * Upsert multiple Datastore entities.
 *
 * @param DatastoreClient $datastore
 * @param array <\Google\Cloud\Datastore\Entity> $tasks
 */
function upsert_multi(DatastoreClient $datastore, array $tasks)
{
    // [START upsert_multi]
    $datastore->upsertBatch($tasks);
    // [END upsert_multi]
}

/**
 * Lookup multiple entities.
 *
 * @param DatastoreClient $datastore
 * @param array <Key> $keys
 * @return array <\Google\Cloud\Datastore\Entity>
 */
function lookup_multi(DatastoreClient $datastore, array $keys)
{
    // [START lookup_multi]
    $result = $datastore->lookupBatch($keys);
    if (isset($result['found'])) {
        // $result['found'] is an array of entities.
    } else {
        // No entities found.
    }
    // [END lookup_multi]
    return $result;
}

/**
 * Delete multiple Datastore entities with the given keys.
 *
 * @param DatastoreClient $datastore
 * @param array <Key> $keys
 */
function delete_multi(DatastoreClient $datastore, array $keys)
{
    // [START delete_multi]
    $datastore->deleteBatch($keys);
    // [END delete_multi]
}

/**
 * Create a complete Datastore key.
 *
 * @param DatastoreClient $datastore
 * @return Key
 */
function create_complete_key(DatastoreClient $datastore)
{
    // [START names_key]
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
function create_incomplete_key(DatastoreClient $datastore)
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
function create_key_with_parent(DatastoreClient $datastore)
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
function create_key_with_multi_level_parent(DatastoreClient $datastore)
{
    // [START key_with_multi_level_parent]
    $taskKey = $datastore->key('User', 'alice')
        ->pathElement('TaskList', 'default')
        ->pathElement('Task', 'sampleTask');
    // [END key_with_multi_level_parent]
    return $taskKey;
}

/**
 * Create a Datastore entity, giving the excludeFromIndexes option.
 *
 * @param DatastoreClient $datastore
 * @param Key $key
 * @return \Google\Cloud\Datastore\Entity
 */
function create_entity_with_option(DatastoreClient $datastore, Key $key)
{
    // [START entity_with_option]
    $task = $datastore->entity(
        $key,
        [
            'category' => 'Personal',
            'created' => new \DateTime(),
            'done' => false,
            'percent_complete' => 10.0,
            'description' => 'Learn Cloud Datastore'
        ],
        ['excludeFromIndexes' => ['description']]
    );
    // [END entity_with_option]
    return $task;
}

/**
 * Create a Datastore entity with some array properties.
 *
 * @param DatastoreClient $datastore
 * @param Key $key
 * @return \Google\Cloud\Datastore\Entity
 */
function create_entity_with_array_property(DatastoreClient $datastore, Key $key)
{
    // [START entity_with_array_property]
    $task = $datastore->entity(
        $key,
        [
            'tags' => ['fun', 'programming'],
            'collaborators' => ['alice', 'bob']
        ]
    );
    // [END entity_with_option]
    return $task;
}
