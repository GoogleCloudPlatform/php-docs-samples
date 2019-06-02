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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_dml_getting_started_update]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;

/**
 * Performs a read-write transaction to update two sample records in the
 * database.
 *
 * This will transfer 200,000 from the `MarketingBudget` field for the second
 * Album to the first Album. If the `MarketingBudget` for the second Album is
 * too low, it will raise an exception.
 *
 * Before running this sample, you will need to run the `update_data` sample
 * to populate the fields.
 * Example:
 * ```
 * write_data_with_dml_transaction($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function write_data_with_dml_transaction($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->runTransaction(function (Transaction $t) use ($spanner) {
        // Transfer marketing budget from one album to another. We do it in a transaction to
        // ensure that the transfer is atomic.
        $transferAmount = 200000;

        $results = $t->execute(
            "SELECT MarketingBudget from Albums WHERE SingerId = 2 and AlbumId = 2"
        );
        $resultsRow = $results->rows()->current();
        $album2budget = $resultsRow['MarketingBudget'];

        // Transaction will only be committed if this condition still holds at the time of
        // commit. Otherwise it will be aborted and the callable will be rerun by the
        // client library.
        if ($album2budget >= $transferAmount) {
            $results = $t->execute(
                "SELECT MarketingBudget from Albums WHERE SingerId = 1 and AlbumId = 1"
            );
            $resultsRow = $results->rows()->current();
            $album1budget = $resultsRow['MarketingBudget'];

            $album2budget -= $transferAmount;
            $album1budget += $transferAmount;

            // Update the albums
            $t->executeUpdate(
                "UPDATE Albums "
                . "SET MarketingBudget = @AlbumBudget "
                . "WHERE SingerId = 1 and AlbumId = 1",
                [
                    'parameters' => [
                        'AlbumBudget' => $album1budget
                    ]
                ]
            );
            $t->executeUpdate(
                "UPDATE Albums "
                . "SET MarketingBudget = @AlbumBudget "
                . "WHERE SingerId = 2 and AlbumId = 2",
                [
                    'parameters' => [
                        'AlbumBudget' => $album2budget
                    ]
                ]
            );

            $t->commit();

            print('Transaction complete.' . PHP_EOL);
        }
    });
}
// [END spanner_dml_getting_started_update]
