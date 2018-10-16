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

if (isset($argv)) {
    return print("This file is for example only and cannot be executed\n");
}

# [START bigquery_get_table]
use Google\Cloud\BigQuery\BigQueryClient;

/** Uncomment and populate these variables in your code */
//$projectId = 'The Google project ID';
//$datasetId = 'The BigQuery dataset ID';
//$tableId   = 'The BigQuery table ID';

$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$dataset = $bigQuery->dataset($datasetId);
$table = $dataset->table($tableId);
# [END bigquery_get_table]
return $table;
