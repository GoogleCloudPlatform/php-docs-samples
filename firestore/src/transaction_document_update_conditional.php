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
 * Return information from your transaction.
 *
 * @param string $projectId The Google Cloud Project ID
 */
function transaction_document_update_conditional(string $projectId): void
{
    // Create the Cloud Firestore client
    $db = new FirestoreClient([
        'projectId' => $projectId,
    ]);
    # [START fs_return_info_transaction]
    # [START firestore_transaction_document_update_conditional]
    $cityRef = $db->collection('samples/php/cities')->document('SF');
    $transactionResult = $db->runTransaction(function (Transaction $transaction) use ($cityRef) {
        $snapshot = $transaction->snapshot($cityRef);
        $newPopulation = $snapshot['population'] + 1;
        if ($newPopulation <= 1000000) {
            $transaction->update($cityRef, [
                ['path' => 'population', 'value' => $newPopulation]
            ]);
            return true;
        } else {
            return false;
        }
    });

    if ($transactionResult) {
        printf('Population updated successfully.' . PHP_EOL);
    } else {
        printf('Sorry! Population is too big.' . PHP_EOL);
    }
    # [END firestore_transaction_document_update_conditional]
    # [END fs_return_info_transaction]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
