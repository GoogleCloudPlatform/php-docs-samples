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
if (count($argv) != 4) {
    return printf("Usage: php %s PROJECT_ID DATASET_ID TABLE_ID\n", __FILE__);
}

list($_, $projectId, $datasetId, $tableId) = $argv;
# [START bigquery_load_table_gcs_parquet_truncate]
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\ExponentialBackoff;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $datasetId = 'The BigQuery dataset ID';
// $tableID = 'The BigQuery table ID';

// instantiate the bigquery table service
$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$table = $bigQuery->dataset($datasetId)->table($tableId);

// create the import job
$gcsUri = 'gs://cloud-samples-data/bigquery/us-states/us-states.parquet';
$loadConfig = $table->loadFromStorage($gcsUri)->sourceFormat('PARQUET')->writeDisposition('WRITE_TRUNCATE');
$job = $table->runJob($loadConfig);

// poll the job until it is complete
$backoff = new ExponentialBackoff(10);
$backoff->execute(function () use ($job) {
    print('Waiting for job to complete' . PHP_EOL);
    $job->reload();
    if (!$job->isComplete()) {
        throw new Exception('Job has not yet completed', 500);
    }
});

// check if the job has errors
if (isset($job->info()['status']['errorResult'])) {
    $error = $job->info()['status']['errorResult']['message'];
    printf('Error running job: %s' . PHP_EOL, $error);
} else {
    print('Data imported successfully' . PHP_EOL);
}
# [END bigquery_load_table_gcs_parquet_truncate]
