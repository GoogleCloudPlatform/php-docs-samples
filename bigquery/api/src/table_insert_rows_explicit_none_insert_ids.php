<?php
/**
 * Copyright 2022 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\BigQuery;

# [START bigquery_table_insert_rows_explicit_none_insert_ids]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Insert rows into the given table with explicitly giving row ids.
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId The BigQuery table ID.
 * @param string $rowData1 Json encoded data to insert.
 * @param string $rowData2 Json encoded data to insert. For eg,
 *    $rowData1 = json_encode([
 *        "field1" => "value1",
 *        "field2" => "value2"
 *    ]);
 *    $rowData2 = json_encode([
 *        "field1" => "value1",
 *        "field2" => "value2"
 *    ]);
 */
function table_insert_rows_explicit_none_insert_ids(
    string $projectId,
    string $datasetId,
    string $tableId,
    string $rowData1,
    string $rowData2
): void {
    $bigQuery = new BigQueryClient([
        'projectId' => $projectId,
    ]);
    $dataset = $bigQuery->dataset($datasetId);
    $table = $dataset->table($tableId);

    $rowData1 = json_decode($rowData1, true);
    $rowData2 = json_decode($rowData2, true);
    // Omitting insert Id's in following rows.
    $rows = [
        ['data' => $rowData1],
        ['data' => $rowData2]
    ];
    $insertResponse = $table->insertRows($rows);

    if ($insertResponse->isSuccessful()) {
        printf('Rows successfully inserted into table without insert ids' . PHP_EOL);
    } else {
        foreach ($insertResponse->failedRows() as $row) {
            foreach ($row['errors'] as $error) {
                printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
            }
        }
    }
}
# [END bigquery_table_insert_rows_explicit_none_insert_ids]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
