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
 * Batch write.
 * ```
 * batch_write('your-project-id');
 * ```
 */
function batch_write($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_batch_write]
    $batch = $db->batch();

    # Set the data for NYC
    $nycRef = $db->collection('cities')->document('NYC');
    $batch->set($nycRef, [
        'name' => 'New York City'
    ]);

    # Update the population for SF
    $sfRef = $db->collection('cities')->document('SF');
    $batch->update($sfRef, [
        ['path' => 'population', 'value' => 1000000]
    ]);

    # Delete LA
    $laRef = $db->collection('cities')->document('LA');
    $batch->delete($laRef);

    # Commit the batch
    $batch->commit();
    # [END fs_batch_write]
    printf('Batch write successfully completed.' . PHP_EOL);
}
