<?php
/**
 * Copyright 2016 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/api/README.md
 */

namespace Google\Cloud\Samples\Spanner;

# [START spanner_read_write_transaction]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;
use UnexpectedValueException;

/**
 * Performs a read-write transaction to update two sample records in the
 * database.
 *
 * This will transfer 200,000 from the `MarketingBudget` field for the second
 * Album to the first Album. If the `MarketingBudget` is too low, it will
 * raise an exception.
 *
 * Before running this sample, you will need to run the `update_data` sample
 * to populate the fields.
 * Example:
 * ```
 * read_write_transaction($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function read_write_transaction($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->runTransaction(function(Transaction $t) use ($spanner) {
        // Read the second album's budget.
        $secondAlbumKey = [2,2];
        $secondAlbumKeySet = $spanner->keySet(['keys' => [$secondAlbumKey]]);
        $secondAlbumResult = $t->read(
            'Albums',
            $secondAlbumKeySet,
            ['MarketingBudget'],
            ['limit' => 1]
        );

        $firstRow = $secondAlbumResult->rows()->current();
        $secondAlbumBudget = $firstRow['MarketingBudget'];
        if ($secondAlbumBudget < 300000) {
            // Throwing an exception will automatically roll back the transaction.
            throw new UnexpectedValueException(
                'The second album doesn\'t have enough funds to transfer'
            );
        }

        $firstAlbumKey = [1,1];
        $firstAlbumKeySet = $spanner->keySet(['keys' => [$firstAlbumKey]]);
        $firstAlbumResult = $t->read(
            'Albums',
            $firstAlbumKeySet,
            ['MarketingBudget'],
            ['limit' => 1]
        );

        // Read the first album's budget.
        $firstRow = $firstAlbumResult->rows()->current();
        $firstAlbumBudget = $firstRow['MarketingBudget'];

        // Update the budgets.
        $transferAmmount = 20000;
        $secondAlbumBudget -= $transferAmmount;
        $firstAlbumBudget += $transferAmmount;
        printf('Setting first album\'s budget to %s and the second album\'s ' .
            'budget to %s.' . PHP_EOL, $firstAlbumBudget, $secondAlbumBudget);

        // Update the rows.
        $t->updateBatch('Albums', [
            ['SingerId' => 1, 'AlbumId' => 1, 'MarketingBudget' => $firstAlbumBudget],
            ['SingerId' => 2, 'AlbumId' => 2, 'MarketingBudget' => $secondAlbumBudget],
        ]);

        // Commit the transaction!
        $t->commit();
    });

    print('Transaction complete.' . PHP_EOL);
}
# [END spanner_read_write_transaction]
