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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigquery/api/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 4 || count($argv) > 5) {
    return printf("Usage: php %s PROJECT_ID DATASET_ID TABLE_ID [START_INDEX]\n", __FILE__);
}
list($_, $projectId, $datasetId, $tableId) = $argv;
$startIndex = isset($argv[4]) ? $argv[4] : 0;


# [START bigquery_browse_table]
use Google\Cloud\BigQuery\BigQueryClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $datasetId = 'The BigQuery dataset ID';
// $tableId   = 'The BigQuery table ID';
// $startIndex = 0;

$maxResults = 10;

$options = [
    'maxResults' => $maxResults,
    'startIndex' => $startIndex
];
$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$dataset = $bigQuery->dataset($datasetId);
$table = $dataset->table($tableId);
$numRows = 0;
foreach ($table->rows($options) as $row) {
    print('---');
    foreach ($row as $column => $value) {
        printf('%s: %s' . PHP_EOL, $column, $value);
    }
    $numRows++;
}
# [END bigquery_browse_table]
return $numRows;
