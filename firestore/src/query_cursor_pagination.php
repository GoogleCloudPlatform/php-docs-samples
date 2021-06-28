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
 * Paginate using cursor queries.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function query_cursor_pagination(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_paginated_query_cursor]
    # [START firestore_query_cursor_pagination]
    $citiesRef = $db->collection('samples/php/cities');
    $firstQuery = $citiesRef->orderBy('population')->limit(3);

    # Get the last document from the results
    $documents = $firstQuery->documents();
    $lastPopulation = 0;
    foreach ($documents as $document) {
        $lastPopulation = $document['population'];
    }

    # Construct a new query starting at this document
    # Note: this will not have the desired effect if multiple cities have the exact same population value
    $nextQuery = $citiesRef->orderBy('population')->startAfter([$lastPopulation]);
    $snapshot = $nextQuery->documents();
    # [END firestore_query_cursor_pagination]
    # [END fs_paginated_query_cursor]
    foreach ($snapshot as $document) {
        printf('Document %s returned by paginated query cursor.' . PHP_EOL, $document->id());
    }
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
