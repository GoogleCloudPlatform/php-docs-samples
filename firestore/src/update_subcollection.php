<?php
/**
 * Copyright 2019 Google LLC.
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
 * Increments a randomly picked shard of distributed counter.
 * ```
 * update_distributed_counter('your-project-id');
 * ```
 */
function update_distributed_counter($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    $ref = $db->collection('Shards_collection')->document('Distributed_counters');
    # [START fs_update_distributed_counter]
    $colRef = $ref->collection('SHARDS');
    $numShards = 0;
    $docCollection = $colRef->documents();
    foreach ($docCollection as $doc) {
        $numShards++;
    }
    $shardIdx = random_int(0, $numShards-1);
    $doc = $colRef->document($shardIdx);
    $doc->update([
        ['path' => 'Cnt', 'value' => FieldValue::increment(1)]
    ]);
    # [END fs_update_distributed_counter]
}
