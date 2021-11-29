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
use Google\Cloud\Firestore\FieldValue;

/**
 * Update field with server timestamp.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function data_set_server_timestamp(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    $docRef = $db->collection('samples/php/objects')->document('some-id');
    $docRef->set([
        'timestamp' => 'N/A'
    ]);
    # [START fs_update_server_timestamp]
    # [START firestore_data_set_server_timestamp]
    $docRef = $db->collection('samples/php/objects')->document('some-id');
    $docRef->update([
        ['path' => 'timestamp', 'value' => FieldValue::serverTimestamp()]
    ]);
    # [END firestore_data_set_server_timestamp]
    # [END fs_update_server_timestamp]
    printf('Updated the timestamp field of the some-id document in the objects collection.' . PHP_EOL);
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
