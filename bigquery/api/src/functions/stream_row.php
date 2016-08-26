<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
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

# [START stream_row]
use Google\Cloud\ServiceBuilder;

/**
 * Stream a row of data into your BigQuery table
 * Example:
 * ```
 * $data = [
 *     "field1" => "value1",
 *     "field2" => "value2",
 * ];
 * stream_row($projectId, $datasetId, $tableId, $data);
 * ```.
 *
 * @param string $projectId The Google project ID.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId   The BigQuery table ID.
 * @param string $data      An associative array representing a row of data.
 * @param string $insertId  An optional unique ID to guarantee data consistency.
 */
function stream_row($projectId, $datasetId, $tableId, $data, $insertId = null)
{
    // instantiate the bigquery table service
    $builder = new ServiceBuilder([
        'projectId' => $projectId,
    ]);
    $bigQuery = $builder->bigQuery();
    $dataset = $bigQuery->dataset($datasetId);
    $table = $dataset->table($tableId);

    $insertResponse = $table->insertRows([
        ['insertId' => $insertId, 'data' => $data],
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
# [END stream_row]
