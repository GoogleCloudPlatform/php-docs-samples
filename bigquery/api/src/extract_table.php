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

# [START bigquery_extract_table]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Extracts the given table as json to given GCS bucket.
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId The BigQuery table ID.
 * @param string $bucketName Bucket name in Google Cloud Storage
 */
function extract_table(
    string $projectId,
    string $datasetId,
    string $tableId,
    string $bucketName
): void {
    $bigQuery = new BigQueryClient([
      'projectId' => $projectId,
    ]);
    $dataset = $bigQuery->dataset($datasetId);
    $table = $dataset->table($tableId);
    $destinationUri = "gs://{$bucketName}/{$tableId}.json";
    // Define the format to use. If the format is not specified, 'CSV' will be used.
    $format = 'NEWLINE_DELIMITED_JSON';
    // Create the extract job
    $extractConfig = $table->extract($destinationUri)->destinationFormat($format);
    // Run the job
    $job = $table->runJob($extractConfig);  // Waits for the job to complete
    printf('Exported %s to %s' . PHP_EOL, $table->id(), $destinationUri);
}
# [END bigquery_extract_table]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
