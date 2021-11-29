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
 * Set multiple cursor conditions
 *
 * @param string $projectId The Google Cloud Project ID
 */
function query_cursor_start_at_field_value_multi(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_multiple_cursor_conditions]
    # [START firestore_query_cursor_start_at_field_value_multi]
    // Will return all Springfields
    $query1 = $db
        ->collection('samples/php/cities')
        ->orderBy('name')
        ->orderBy('state')
        ->startAt(['Springfield']);

    // Will return "Springfield, Missouri" and "Springfield, Wisconsin"
    $query2 = $db
        ->collection('samples/php/cities')
        ->orderBy('name')
        ->orderBy('state')
        ->startAt(['Springfield', 'Missouri']);
    # [END firestore_query_cursor_start_at_field_value_multi]
    # [END fs_multiple_cursor_conditions]
    $snapshot1 = $query1->documents();
    foreach ($snapshot1 as $document) {
        printf('Document %s returned by start at Springfield query.' . PHP_EOL, $document->id());
    }
    $snapshot2 = $query2->documents();
    foreach ($snapshot2 as $document) {
        printf('Document %s returned by start at Springfield, Missouri query.' . PHP_EOL, $document->id());
    }
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
