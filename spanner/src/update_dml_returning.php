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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_update_dml_returning]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Update the given database using DML returning.
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function update_dml_returning(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $transaction = $database->transaction();

    // DML returning sql update query
    $result = $transaction->execute(
        "UPDATE Albums "
        . "SET MarketingBudget = MarketingBudget * 2 "
        . "WHERE SingerId = 1 and AlbumId = 1 "
        . "THEN RETURN MarketingBudget"
    );
    foreach ($result->rows() as $row) {
        printf('MarketingBudget: %s' . PHP_EOL, $row['MarketingBudget']);
    }
    printf(
        'Updated row(s) count: %d' . PHP_EOL,
        $result->stats()['rowCountExact']
    );
    $transaction->commit();
}
// [END spanner_update_dml_returning]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
