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

use Google\Cloud\Firestore\FieldValue;
use Google\Cloud\Firestore\FirestoreClient;

/**
 * Update a document array field.
 * ```
 * update_doc_array('your-project-id');
 * ```
 */
function update_doc_array($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_update_doc_array]
    $cityRef = $db->collection('cities')->document('DC');

    // Atomically add a new region to the "regions" array field.
    $cityRef->update([
        ['path' => 'regions', 'value' => FieldValue::arrayUnion(['greater_virginia'])]
    ]);

    // Atomically remove a region from the "regions" array field.
    $cityRef->update([
        ['path' => 'regions', 'value' => FieldValue::arrayRemove(['east_coast'])]
    ]);
    # [END fs_update_doc_array]
    printf('Updated the regions field of the DC document in the cities collection.' . PHP_EOL);
}
