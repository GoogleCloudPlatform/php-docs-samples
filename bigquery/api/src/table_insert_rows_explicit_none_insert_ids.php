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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 6) {
    return printf("Usage: php %s PROJECT_ID DATASET_ID TABLE_ID ROW_DATA_1 ROW_DATA_2\n", __FILE__);
}
list($_, $projectId, $datasetId, $tableId, $rowData1, $rowData2) = $argv;
$rowData1 = json_decode($rowData1, true);
$rowData2 = json_decode($rowData2, true);

# [START bigquery_table_insert_rows_explicit_none_insert_ids]
use Google\Cloud\BigQuery\BigQueryClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $datasetId = 'The BigQuery dataset ID';
// $tableId = 'The BigQuery table ID';
// $rowData1 = [
//     "field1" => "value1",
//     "field2" => "value2"
// ];
// $rowData2 = [
//     "field1" => "value1",
//     "field2" => "value2"
// ];

$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$dataset = $bigQuery->dataset($datasetId);
$table = $dataset->table($tableId);

// Omitting insert Id's in following rows.
$rows = [
  ['data' => $rowData1],
  ['data' => $rowData2]
];
$insertResponse = $table->insertRows($rows);

if ($insertResponse->isSuccessful()) {
    printf('Rows successfully inserted into table without insert id\'s');
} else {
    foreach ($insertResponse->failedRows() as $row) {
        foreach ($row['errors'] as $error) {
            printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
        }
    }
}
# [END bigquery_table_insert_rows_explicit_none_insert_ids]
