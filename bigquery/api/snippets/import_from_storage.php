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

if (count($argv) != 6) {
    return print("Usage: php snippets/import_from_storage.php PROJECT_ID DATASET_ID TABLE_ID BUCKET_NAME OBJECT_NAME\n");
}

list($_, $projectId, $datasetId, $tableId, $bucketName, $objectName) = $argv;

# [START bigquery_load_table_gcs_csv]
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\ExponentialBackoff;

/** Uncomment and populate these variables in your code */
// $projectId  = 'The Google project ID';
// $datasetId  = 'The BigQuery dataset ID';
// $tableId    = 'The BigQuery table ID';
// $bucketName = 'The Cloud Storage bucket Name';
// $objectName = 'The Cloud Storage object Name';

// instantiate the bigquery table service
$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$dataset = $bigQuery->dataset($datasetId);
$table = $dataset->table($tableId);
// load the storage object
$storage = new StorageClient([
    'projectId' => $projectId,
]);
$object = $storage->bucket($bucketName)->object($objectName);
// create the import job
$loadConfig = $table->loadFromStorage($object);
// determine the source format from the object name
if ('.backup_info' === substr($objectName, -12)) {
    $loadConfig->sourceFormat('DATASTORE_BACKUP');
} elseif ('.json' === substr($objectName, -5)) {
    $loadConfig->sourceFormat('NEWLINE_DELIMITED_JSON');
}
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
# [END bigquery_load_table_gcs_csv]
