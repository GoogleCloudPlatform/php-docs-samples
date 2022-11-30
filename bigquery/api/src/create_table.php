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

# [START bigquery_create_table]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Creates a table with the given ID and Schema
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId The BigQuery table ID.
 * @param string $fields Json Encoded string of schema of the table. For eg,
 *    $fields = json_encode([
 *     [
 *         'name' => 'field1',
 *         'type' => 'string',
 *         'mode' => 'required'
 *     ],
 *     [
 *         'name' => 'field2',
 *         'type' => 'integer'
 *     ],
 *    ]);
 */

function create_table(
    string $projectId,
    string $datasetId,
    string $tableId,
    string $fields
): void {
    $bigQuery = new BigQueryClient([
      'projectId' => $projectId,
    ]);
    $dataset = $bigQuery->dataset($datasetId);
    $fields = json_decode($fields);
    $schema = ['fields' => $fields];
    $table = $dataset->createTable($tableId, ['schema' => $schema]);
    printf('Created table %s' . PHP_EOL, $tableId);
}
# [END bigquery_create_table]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
