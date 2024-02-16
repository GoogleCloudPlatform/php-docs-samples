<?php
/**
 * Copyright 2023 Google Inc.
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
 * Create a query with distinctOn.
 *
 * @param DatastoreClient $datastore
 *
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
    print_r($query);

    $result = $datastore->runQuery($query);
    $num = 0;
    $entities = [];
    foreach ($result as $e) {
        $entities[] = $e;
        $num += 1;
    }

    printf('Found %s records', $num);
    print_r($entities);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
