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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\BigQuery;

# [START bigquery_table_insert_rows]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Stream data into bigquery
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId The BigQuery table ID.
 * @param string $data Json encoded data For eg,
 *    $data = json_encode([
 *       "field1" => "value1",
 *       "field2" => "value2",
 *    ]);
 */
function stream_row(
    string $projectId,
    string $datasetId,
    string $tableId,
    string $data
): void {
    // instantiate the bigquery table service
    $bigQuery = new BigQueryClient([
      'projectId' => $projectId,
    ]);
    $dataset = $bigQuery->dataset($datasetId);
    $table = $dataset->table($tableId);

    $data = json_decode($data, true);
    $insertResponse = $table->insertRows([
      ['data' => $data],
      // additional rows can go here
    ]);
    if ($insertResponse->isSuccessful()) {
        print('Data streamed into BigQuery successfully' . PHP_EOL);
    } else {
        foreach ($insertResponse->failedRows() as $row) {
            foreach ($row['errors'] as $error) {
                printf('%s: %s' . PHP_EOL, $error['reason'], $error['message']);
            }
        }
    }
}
# [END bigquery_table_insert_rows]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
