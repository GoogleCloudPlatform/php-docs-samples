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
 * Run the given projection query and collect the projected properties.
 *
 * @param string $namespaceId
 * @param Query $query
 */
function run_projection_query(Query $query = null, string $namespaceId = null)
{
    $datastore = new DatastoreClient(['namespaceId' => $namespaceId]);
    if (!isset($query)) {
        $query = $datastore->query()
            ->kind('Task')
            ->projection(['priority', 'percent_complete']);
    }

    // [START datastore_run_query_projection]
    $priorities = [];
    $percentCompletes = [];
    $result = $datastore->runQuery($query);
    /* @var Entity $task */
    foreach ($result as $task) {
        $priorities[] = $task['priority'];
        $percentCompletes[] = $task['percent_complete'];
    }
    // [END datastore_run_query_projection]

    print_r([$priorities, $percentCompletes]);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
