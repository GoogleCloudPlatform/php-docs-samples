<?php
/**
 * Copyright 2019 Google LLC
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

// [START spanner_dml_batch_update]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;

/**
 * Updates sample data in the database with Batch DML.
 *
 * This requires the `MarketingBudget` column which must be created before
 * running this sample. You can add the column by running the `add_column`
 * sample or by running this DDL statement against your database:
 *
 *     ALTER TABLE Albums ADD COLUMN MarketingBudget INT64
 *
 * Example:
 * ```
 * update_data_with_batch_dml($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function update_data_with_batch_dml($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $batchDmlResult = $database->runTransaction(function (Transaction $t) {
        $result = $t->executeUpdateBatch([
            [
                'sql' => "INSERT INTO Albums "
                . "(SingerId, AlbumId, AlbumTitle, MarketingBudget) "
                . "VALUES (1, 3, 'Test Album Title', 10000)"
            ],
            [
                'sql' => "UPDATE Albums "
                . "SET MarketingBudget = MarketingBudget * 2 "
                . "WHERE SingerId = 1 and AlbumId = 3"
            ],
        ]);
        $t->commit();
        $rowCounts = count($result->rowCounts());
        printf('Executed %s SQL statements using Batch DML.' . PHP_EOL,
            $rowCounts);
    });
}
// [END spanner_dml_batch_update]
