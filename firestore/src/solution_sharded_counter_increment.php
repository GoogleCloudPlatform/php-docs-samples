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
 *
 * @param string $projectId The Google Cloud Project ID
 */
function solution_sharded_counter_increment(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);

    # [START fs_update_distributed_counter]
    # [START firestore_solution_sharded_counter_increment]
    $ref = $db->collection('samples/php/distributedCounters');
    $numShards = 0;
    $docCollection = $ref->documents();
    foreach ($docCollection as $doc) {
        $numShards++;
    }
    $shardIdx = random_int(0, $numShards - 1);
    $doc = $ref->document($shardIdx);
    $doc->update([
        ['path' => 'Cnt', 'value' => FieldValue::increment(1)]
    ]);
    # [END firestore_solution_sharded_counter_increment]
    # [END fs_update_distributed_counter]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
