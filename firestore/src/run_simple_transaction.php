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
use Google\Cloud\Firestore\Transaction;

/**
 * Run a simple transaction.
 * ```
 * run_simple_transaction('your-project-id');
 * ```
 */
function run_simple_transaction($projectId)
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_run_simple_transaction]
    $cityRef = $db->collection('cities')->document('SF');
    $db->runTransaction(function (Transaction $transaction) use ($cityRef) {
        $snapshot = $transaction->snapshot($cityRef);
        $newPopulation = $snapshot['population'] + 1;
        $transaction->update($cityRef, [
            ['path' => 'population', 'value' => $newPopulation]
        ]);
    });
    # [END fs_run_simple_transaction]
    printf('Ran a simple transaction to update the population field in the SF document in the cities collection.' . PHP_EOL);
}
