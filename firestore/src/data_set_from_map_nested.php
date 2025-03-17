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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/firestore/README.md
 */

namespace Google\Cloud\Samples\Firestore;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Core\Timestamp;
use DateTime;

/**
 * Set document data with different data types.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function data_set_from_map_nested(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    // Set the reference document
    $db->collection('samples/php/data')->document('two')->set(['foo' => 'bar']);
    # [START firestore_data_set_from_map_nested]
    $data = [
        'stringExample' => 'Hello World',
        'booleanExample' => true,
        'numberExample' => 3.14159265,
        'dateExample' => new Timestamp(new DateTime()),
        'arrayExample' => [5, true, 'hello'],
        'nullExample' => null,
        'objectExample' => ['a' => 5, 'b' => true],
        'documentReferenceExample' => $db->collection('samples/php/data')->document('two'),
    ];
    $db->collection('samples/php/data')->document('one')->set($data);
    printf('Set multiple data-type data for the one document in the data collection.' . PHP_EOL);
    # [END firestore_data_set_from_map_nested]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
