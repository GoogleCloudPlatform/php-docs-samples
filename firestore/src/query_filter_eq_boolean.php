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
 * Create a query that gets documents where capital=true.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function query_filter_eq_boolean(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_create_query_capital]
    # [START firestore_query_filter_eq_boolean]
    $citiesRef = $db->collection('samples/php/cities');
    $query = $citiesRef->where('capital', '=', true);
    $snapshot = $query->documents();
    foreach ($snapshot as $document) {
        printf('Document %s returned by query capital=true' . PHP_EOL, $document->id());
    }
    # [END firestore_query_filter_eq_boolean]
    # [END fs_create_query_capital]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
