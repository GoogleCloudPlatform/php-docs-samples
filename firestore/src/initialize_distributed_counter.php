<?php
/**
 * Copyright 2019 Google Inc.
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
 * Creates the specified multiple shards as a subcollection.
 * ```
 * initialize_distributed_counter('your-project-id');
 * ```
 */
function initialize_distributed_counter($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    $ref = $db->collection('Shards_collection')->document('Distributed_counters');
    # [START fs_initialize_distributed_counter]
    $numShards = 10;
    $colRef = $ref->collection('SHARDS');
    for ($i = 0; $i < $numShards; $i++) {
        $doc = $colRef->document($i);
        $doc->set(['Cnt' => 0]);
    }
    # [END fs_initialize_distributed_counter]
}
