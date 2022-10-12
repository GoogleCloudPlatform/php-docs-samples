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

if (count($argv) != 4) {
    return printf("Usage: php %s PROJECT_ID DATASET_ID TABLE_ID\n", __FILE__);
}
list($_, $projectId, $datasetId, $tableId) = $argv;

# [START bigquery_add_column_load_append]
use Google\Cloud\BigQuery\BigQueryClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $datasetId = 'The BigQuery dataset ID';
// $tableId = 'Table ID of the table in dataset';

$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$dataset = $bigQuery->dataset($datasetId);
$table = $dataset->table($tableId);
// In this example, the existing table contains only the 'Name' and 'Title'.
// A new column 'Description' gets added after load job.

$schema = [
  'fields' => [
      ['name' => 'name', 'type' => 'string', 'mode' => 'nullable'],
      ['name' => 'title', 'type' => 'string', 'mode' => 'nullable'],
      ['name' => 'description', 'type' => 'string', 'mode' => 'nullable']
  ]
];

$source = __DIR__ . '/../test/data/test_data_extra_column.csv';

$loadConfig = $table->load(fopen($source, 'r'));
$loadConfig->destinationTable($table);
$loadConfig->schema($schema);
$loadConfig->schemaUpdateOptions(['ALLOW_FIELD_ADDITION']);
$loadConfig->sourceFormat('CSV');
$loadConfig->writeDisposition('WRITE_APPEND');

$job = $bigQuery->runJob($loadConfig);
printf('New column added');
# [END bigquery_add_column_load_append]
