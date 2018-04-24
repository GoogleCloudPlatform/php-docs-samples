<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\Samples\BigQuery;

use Exception;
# [START bigquery_load_table_gcs_csv]
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\ExponentialBackoff;

/**
 * @param string $projectId  The Google project ID.
 * @param string $datasetId  The BigQuery dataset ID.
 * @param string $tableId    The BigQuery table ID.
 * @param string $bucketName The Cloud Storage bucket Name.
 * @param string $objectName The Cloud Storage object Name.
 */
function import_from_storage($projectId, $datasetId, $tableId, $bucketName, $objectName)
{
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
}
# [END bigquery_load_table_gcs_csv]
