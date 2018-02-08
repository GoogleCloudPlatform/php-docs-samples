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
 * ```
 * fs_add_data_2();
 * ```
 *
 */
function fs_add_data_2()
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient();
    # [START fs_add_data_2]
    $doc_ref = $db->collection('users')->document('aturing');
    $doc_ref->set([
    	'first' => 'Alan',
        'middle' => 'Mathison',
    	'last' => 'Turing',
    	'born' => 1912
    ]);
    printf('Added data to the aturing document in the users collection.' . PHP_EOL);
    # [END fs_add_data_2]
}
