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
# [START bigquery_extract_table]
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\ExponentialBackoff;

/**
 * @param string $projectId  The Google project ID.
 * @param string $datasetId  The BigQuery dataset ID.
 * @param string $tableId    The BigQuery table ID.
 * @param string $bucketName The Cloud Storage bucket Name.
 * @param string $objectName The Cloud Storage object Name.
 * @param string $format     The extract format, either CSV or JSON.
 */
function extract_table($projectId, $datasetId, $tableId, $bucketName, $objectName, $format = 'csv')
{
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
}
# [END bigquery_extract_table]
