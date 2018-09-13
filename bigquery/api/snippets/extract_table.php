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

if (count($argv) < 6 || count($argv) > 7) {
    return print("Usage: php snippets/extract_table.php PROJECT_ID DATASET_ID TABLE_ID BUCKET_NAME OBJECT_NAME [FORMAT]\n");
}

list($_, $projectId, $datasetId, $tableId, $bucketName, $objectName) = $argv;
$format = isset($argv[6]) ? $argv[6] : 'csv';

# [START bigquery_extract_table]
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\ExponentialBackoff;

/** Uncomment and populate these variables in your code */
// $projectId  = 'The Google project ID';
// $datasetId  = 'The BigQuery dataset ID';
// $tableId    = 'The BigQuery table ID';
// $bucketName = 'The Cloud Storage bucket Name';
// $objectName = 'The Cloud Storage object Name';
// $format     = 'The extract format, either "csv" or "json"';

$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$dataset = $bigQuery->dataset($datasetId);
$table = $dataset->table($tableId);
// load the storage object
$storage = new StorageClient([
    'projectId' => $projectId,
]);
$destinationObject = $storage->bucket($bucketName)->object($objectName);
// create the extract job
$options = ['destinationFormat' => $format];
$extractConfig = $table->extract($destinationObject, $options);
$job = $table->runJob($extractConfig);
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
    print('Data extracted successfully' . PHP_EOL);
}
# [END bigquery_extract_table]
