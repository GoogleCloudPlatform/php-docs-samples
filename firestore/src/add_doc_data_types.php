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
use Google\Cloud\Core\Timestamp;
use DateTime;

/**
 * Set document data with different data types.
 * ```
 * add_doc_data_types('your-project-id');
 * ```
 */
function add_doc_data_types($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    // Set the reference document
    $db->collection('data')->document('two')->set(['foo' => 'bar']);
    # [START fs_add_doc_data_types]
    $data = [
        'stringExample' => 'Hello World',
        'booleanExample' => true,
        'numberExample' => 3.14159265,
        'dateExample' => new Timestamp(new DateTime()),
        'arrayExample' => array(5, true, 'hello'),
        'nullExample' => null,
        'objectExample' => ['a' => 5, 'b' => true],
        'documentReferenceExample' => $db->collection('data')->document('two'),
    ];
    $db->collection('data')->document('one')->set($data);
    printf('Set multiple data-type data for the one document in the data collection.' . PHP_EOL);
    # [END fs_add_doc_data_types]
}
