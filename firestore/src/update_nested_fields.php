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
 * Update fields in nested data.
 * ```
 * update_nested_fields('your-project-id');
 * ```
 */
function update_nested_fields($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_update_nested_fields]
    // Create an initial document to update
    $frankRef = $db->collection('users')->document('frank');
    $frankRef->set([
        'name' => 'Frank',
        'favorites' => ['food' => 'Pizza', 'color' => 'Blue', 'subject' => 'Recess'],
        'age' => 12
    ]);

    // Update age and favorite color
    $frankRef->update([
        ['path' => 'age', 'value' => 13],
        ['path' => 'favorites.color', 'value' => 'Red']
    ]);
    # [END fs_update_nested_fields]
    printf('Updated the age and favorite color fields of the frank document in the users collection.' . PHP_EOL);
}
