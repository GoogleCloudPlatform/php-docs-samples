<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
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
use InvalidArgumentException;
# [START import_from_file]
use Google\Cloud\ServiceBuilder;
use Google\Cloud\ExponentialBackoff;

/**
 * @param string $projectId The Google project ID.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId   The BigQuery table ID.
 * @param string $source    The path to the source file to import.
 */
function import_from_file($projectId, $datasetId, $tableId, $source)
{
    // determine the import options from the file extension
    $options = [];
    $pathInfo = pathinfo($source) + ['extension' => null];
    if ('csv' === $pathInfo['extension']) {
        $options['jobConfig'] = ['sourceFormat' => 'CSV'];
    } elseif ('json' === $pathInfo['extension']) {
        $options['jobConfig'] = ['sourceFormat' => 'NEWLINE_DELIMITED_JSON'];
    } else {
        throw new InvalidArgumentException('Source format unknown. Must be JSON or CSV');
    }
    // instantiate the bigquery table service
    $builder = new ServiceBuilder([
        'projectId' => $projectId,
    ]);
    $bigQuery = $builder->bigQuery();
    $dataset = $bigQuery->dataset($datasetId);
    $table = $dataset->table($tableId);
    // create the import job
    $job = $table->load(fopen($source, 'r'), $options);
    // poll the job until it is complete
    $backoff = new ExponentialBackoff(10);
    $backoff->execute(function () use ($job) {
        printf('Waiting for job to complete' . PHP_EOL);
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
# [END import_from_file]
