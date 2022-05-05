<?php
/**
 * Copyright 2022 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_postgresql_dml_getting_started_update]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;

/**
 *
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_dml_getting_started_update(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    // Transfer marketing budget from one album to another. We do it in a transaction to
    // ensure that the transfer is atomic.
    $database->runTransaction(function (Transaction $t) {
        $sql = 'SELECT marketingbudget as "MarketingBudget" from Albums WHERE '
        . 'SingerId = 2 and AlbumId = 2';

        $result = $t->execute($sql);
        $row = $result->rows()->current();
        $budgetAlbum2 = $row['MarketingBudget'];
        $transfer = 200000;

        // Transaction will only be committed if this condition still holds at the time of
        // commit. Otherwise it will be aborted.
        if ($budgetAlbum2 > $transfer) {
            $sql = 'SELECT marketingbudget as "MarketingBudget" from Albums WHERE '
            . 'SingerId = 1 and AlbumId = 1';
            $result = $t->execute($sql);
            $row = $result->rows()->current();
            $budgetAlbum1 = $row['MarketingBudget'];

            $budgetAlbum1 += $transfer;
            $budgetAlbum2 -= $transfer;

            $t->executeUpdateBatch([
                [
                    'sql' => 'UPDATE Albums '
                    . 'SET MarketingBudget = $1 '
                    . 'WHERE SingerId = 1 and AlbumId = 1',
                    [
                        'parameters' => [
                            'p1' => $budgetAlbum1
                        ]
                    ]
                ],
                [
                    'sql' => 'UPDATE Albums '
                    . 'SET MarketingBudget = $1 '
                    . 'WHERE SingerId = 2 and AlbumId = 2',
                    [
                        'parameters' => [
                            'p1' => $budgetAlbum2
                        ]
                    ]
                ],
            ]);
            $t->commit();

            print('Marketing budget updated.' . PHP_EOL);
        } else {
            $t->rollback();
        }
    });
}
// [END spanner_postgresql_dml_getting_started_update]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
