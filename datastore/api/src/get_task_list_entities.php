<?php
/**
 * Copyright 2024 Google Inc.
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
use Google\Cloud\Datastore\Query\Query;

/**
 * Run a query with an ancestor inside a transaction.
 *
 * @param string $namespaceId
 */
function get_task_list_entities(string $namespaceId = null)
{
    $datastore = new DatastoreClient(['namespaceId' => $namespaceId]);
    // [START datastore_transactional_single_entity_group_read_only]
    $transaction = $datastore->readOnlyTransaction();
    $taskListKey = $datastore->key('TaskList', 'default');
    $query = $datastore->query()
        ->kind('Task')
        ->hasAncestor($taskListKey);
    $result = $transaction->runQuery($query);
    $taskListEntities = [];
    $num = 0;
    /* @var Entity $task */
    foreach ($result as $task) {
        $taskListEntities[] = $task;
        $num += 1;
    }
    // [END datastore_transactional_single_entity_group_read_only]
    printf('Found %d tasks', $num);
    print_r($taskListEntities);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
