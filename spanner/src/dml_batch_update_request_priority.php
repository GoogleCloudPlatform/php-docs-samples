<?php
/**
 * Copyright 2022 Google LLC
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

// [START spanner_dml_batch_update_request_priority]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;
use Google\Cloud\Spanner\V1\RequestOptions\Priority;

/**
 * Updates sample data in the database with PRIORITY_LOW using Batch DML.
 *
 * This requires the `MarketingBudget` column which must be created before
 * running this sample. You can add the column by running the `add_column`
 * sample or by running this DDL statement against your database:
 *
 *     ALTER TABLE Albums ADD COLUMN MarketingBudget INT64
 *
 * Example:
 * ```
 * dml_batch_update_request_priority($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function dml_batch_update_request_priority(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $batchDmlResult = $database->runTransaction(function (Transaction $t) {
        // Variable to define the Priority of this operation
        // For more information read [
        // the upstream documentation](https://cloud.google.com/spanner/docs/reference/rest/v1/RequestOptions)
        $priority = Priority::PRIORITY_LOW;

        $result = $t->executeUpdateBatch([
            [
                'sql' => 'UPDATE Albums '
                . 'SET MarketingBudget = MarketingBudget * 2 '
                . 'WHERE SingerId = 1 and AlbumId = 3'
            ],
            [
                'sql' => 'UPDATE Albums '
                . 'SET MarketingBudget = MarketingBudget * 2 '
                . 'WHERE SingerId = 2 and AlbumId = 3'
            ],
        ], array('priority' => $priority));
        $t->commit();
        $rowCounts = count($result->rowCounts());
        printf('Executed %s SQL statements using Batch DML with PRIORITY_LOW.' . PHP_EOL,
            $rowCounts);
    });
}
// [END spanner_dml_batch_update_request_priority]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
