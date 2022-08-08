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
 * Add data to a document.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function setup_dataset(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_add_data_1]
    # [START firestore_setup_dataset_pt1]
    $docRef = $db->collection('samples/php/users')->document('alovelace');
    $docRef->set([
        'first' => 'Ada',
        'last' => 'Lovelace',
        'born' => 1815
    ]);
    printf('Added data to the lovelace document in the users collection.' . PHP_EOL);
    # [END firestore_setup_dataset_pt1]
    # [END fs_add_data_1]
    # [START fs_add_data_2]
    # [START firestore_setup_dataset_pt2]
    $docRef = $db->collection('samples/php/users')->document('aturing');
    $docRef->set([
        'first' => 'Alan',
        'middle' => 'Mathison',
        'last' => 'Turing',
        'born' => 1912
    ]);
    printf('Added data to the aturing document in the users collection.' . PHP_EOL);
    # [END firestore_setup_dataset_pt2]
    # [END fs_add_data_2]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
