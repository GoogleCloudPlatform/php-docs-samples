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

namespace Google\Cloud\Samples\Datastore\Tasks;

use Generator;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;

/**
 * Create a Datastore service.
 *
 * @return DatastoreClient
 */
function build_datastore_service()
{
    // [START build_service]
    $datastore = new DatastoreClient();
    // [END build_service]
    $namespace = getenv('CLOUD_DATASTORE_NAMESPACE');
    if ($namespace !== false) {
        $datastore = new DatastoreClient(['namespaceId' => $namespace]);
    }
    return $datastore;
}

// [START add_entity]
/**
 * Create a new task with a given description.
 *
 * @param DatastoreClient $datastore
 * @param $description
 * @return \Google\Cloud\Datastore\Entity
 */
function add_task(DatastoreClient $datastore, $description)
{
    $taskKey = $datastore->key('Task');
    $task = $datastore->entity(
        $taskKey,
        [
            'created' => new \DateTime(),
            'description' => $description,
            'done' => false
        ],
        ['excludeFromIndexes' => ['description']]
    );
    $datastore->insert($task);
    return $task;
}
// [END add_entity]

// [START update_entity]
/**
 * Mark a task with a given id as done.
 *
 * @param DatastoreClient $datastore
 * @param int $taskId
 */
function mark_done(DatastoreClient $datastore, $taskId)
{
    $taskKey = $datastore->key('Task', $taskId);
    $transaction = $datastore->transaction();
    $task = $transaction->lookup($taskKey);
    $task['done'] = true;
    $transaction->upsert($task);
    $transaction->commit();
}
// [END update_entity]

// [START delete_entity]
/**
 * Delete a task with a given id.
 *
 * @param DatastoreClient $datastore
 * @param $taskId
 */
function delete_task(DatastoreClient $datastore, $taskId)
{
    $taskKey = $datastore->key('Task', $taskId);
    $datastore->delete($taskKey);
}
// [END delete_entity]

// [START retrieve_entities]
/**
 * Return a generator for all the tasks in ascending order of creation time.
 *
 * @param DatastoreClient $datastore
 * @return Generator<Entity>
 */
function list_tasks(DatastoreClient $datastore)
{
    $query = $datastore->query()
        ->kind('Task')
        ->order('created');
    return $datastore->runQuery($query);
}
// [END retrieve_entities]
