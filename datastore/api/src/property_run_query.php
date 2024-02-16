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

use DateTime;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\EntityInterface;
use Google\Cloud\Datastore\EntityIterator;
use Google\Cloud\Datastore\Key;
use Google\Cloud\Datastore\Query\GqlQuery;
use Google\Cloud\Datastore\Query\Query;

/**
 * Create and run a property query.
 *
 * @param DatastoreClient $datastore
 *
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
    print_r($properties);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
