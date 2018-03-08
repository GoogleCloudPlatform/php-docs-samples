<?php
/**
 * Copyright 2018 Google Inc.
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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/firestore/README.md
 */

namespace Google\Cloud\Samples\Firestore;

use Google\Cloud\Firestore\FirestoreClient;

/**
 * Create queries using single where clauses.
 * ```
 * simple_queries('your-project-id');
 * ```
 */
function simple_queries($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    $citiesRef = $db->collection('cities');
    # [START fs_simple_queries]
    $stateQuery = $citiesRef->where('state', '=', 'CA');
    $populationQuery = $citiesRef->where('population', '>', 1000000);
    $nameQuery = $citiesRef->where('name', '>=', 'San Francisco');
    # [END fs_simple_queries]
    foreach ($stateQuery->documents() as $document) {
        printf('Document %s returned by query state=CA' . PHP_EOL, $document->id());
    }
    foreach ($populationQuery->documents() as $document) {
        printf('Document %s returned by query population>1000000' . PHP_EOL, $document->id());
    }
    foreach ($nameQuery->documents() as $document) {
        printf('Document %s returned by query name>=San Francisco' . PHP_EOL, $document->id());
    }
}
