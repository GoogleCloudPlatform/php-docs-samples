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

use DateTime;
use Google\Cloud\Datastore\EntityIterator;
// [START datastore_build_service]
use Google\Cloud\Datastore\DatastoreClient;

/**
 * Create a Cloud Datastore client.
 *
 * @param string $projectId
 * @return DatastoreClient
 */
function build_datastore_service($projectId)
{
    $datastore = new DatastoreClient(['projectId' => $projectId]);
    return $datastore;
}
// [END datastore_build_service]

/**
 * Create a Cloud Datastore client with a namespace.
 *
 * @return DatastoreClient
 */
function build_datastore_service_with_namespace()
{
    $namespaceId = getenv('CLOUD_DATASTORE_NAMESPACE');
    if ($namespaceId === false) {
        return new DatastoreClient();
    }
    return new DatastoreClient(['namespaceId' => $namespaceId]);
}

// [START datastore_add_entity]
/**
 * Create a new task with a given description.
 *
 * @param DatastoreClient $datastore
 * @param $description
 * @return Google\Cloud\Datastore\Entity
 */
function add_task(DatastoreClient $datastore, $description)
{
    $taskKey = $datastore->key('Task');
    $task = $datastore->entity(
        $taskKey,
        [
            'created' => new DateTime(),
            'description' => $description,
            'done' => false
        ],
        ['excludeFromIndexes' => ['description']]
    );
    $datastore->insert($task);
    return $task;
}
// [END datastore_add_entity]

// [START datastore_update_entity]
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
// [END datastore_update_entity]

// [START datastore_delete_entity]
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
// [END datastore_delete_entity]

// [START datastore_retrieve_entities]
/**
 * Return an iterator for all the tasks in ascending order of creation time.
 *
 * @param DatastoreClient $datastore
 * @return EntityIterator<Google\Cloud\Datastore\Entity>
 */
function list_tasks(DatastoreClient $datastore)
{
    $query = $datastore->query()
        ->kind('Task')
        ->order('created');
    return $datastore->runQuery($query);
}
// [END datastore_retrieve_entities]
