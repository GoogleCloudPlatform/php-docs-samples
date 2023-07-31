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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\BigQuery;

# [START bigquery_add_column_query_append]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Append a column using a query job.
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId The BigQuery table ID.
 */
function add_column_query_append(
    string $projectId,
    string $datasetId,
    string $tableId
): void {
    $bigQuery = new BigQueryClient([
      'projectId' => $projectId,
    ]);
    $dataset = $bigQuery->dataset($datasetId);
    $table = $dataset->table($tableId);

    // In this example, the existing table contains only the 'Name' and 'Title'.
    // A new column 'Description' gets added after the query job.

    // Define query
    $query = sprintf('SELECT "John" as name, "Unknown" as title, "Dummy person" as description;');

    // Set job configs
    $queryJobConfig = $bigQuery->query($query);
    $queryJobConfig->destinationTable($table);
    $queryJobConfig->schemaUpdateOptions(['ALLOW_FIELD_ADDITION']);
    $queryJobConfig->writeDisposition('WRITE_APPEND');

    // Run query with query job configuration
    $bigQuery->runQuery($queryJobConfig);

    // Print all the columns
    $columns = $table->info()['schema']['fields'];
    printf('The columns in the table are ');
    foreach ($columns as $column) {
        printf('%s ', $column['name']);
    }
}
# [END bigquery_add_column_query_append]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
