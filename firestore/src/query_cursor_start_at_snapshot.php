<?php
/**
 * Copyright 2018 Google LLC.
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
 * Define snapshot start point for a query.
 * ```
 * start_at_snapshot_query_cursor('your-project-id');
 * ```
 */
function start_at_snapshot_query_cursor($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_start_at_snapshot_query_cursor]
    $citiesRef = $db->collection('cities');
    $docRef = $citiesRef->document('SF');
    $snapshot = $docRef->snapshot();

    $query = $citiesRef
        ->orderBy('population')
        ->startAt($snapshot);
    # [END fs_start_at_snapshot_query_cursor]
    $snapshot = $query->documents();
    foreach ($snapshot as $document) {
        printf('Document %s returned by start at SF snapshot query cursor.' . PHP_EOL, $document->id());
    }
}
