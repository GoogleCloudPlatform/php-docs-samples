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
 * Create and run a namespace query.
 *
 * @param DatastoreClient $datastore
 * @param string $start a starting namespace (inclusive)
 * @param string $end an ending namespace (exclusive)
 *
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
    print_r($namespaces);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
