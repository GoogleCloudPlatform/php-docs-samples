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
 * Create a query with a property filter.
 *
 * @param DatastoreClient $datastore
 */
function property_filter(DatastoreClient $datastore)
{
    // [START datastore_property_filter]
    $query = $datastore->query()
        ->kind('Task')
        ->filter('done', '=', false);
    // [END datastore_property_filter]
    print_r($query);

    $result = $datastore->runQuery($query);
    $num = 0;
    $entities = [];
    foreach ($result as $e) {
        $entities[] = $e;
        $num += 1;
    }

    print_r($entities);
    printf('Found %s records.', $num);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
