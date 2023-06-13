<?php
/**
 * Copyright 2023 Google LLC.
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

// [START functions_response_streaming]
use Psr\Http\Message\ServerRequestInterface;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\ExponentialBackoff;

function streamBigQuery(ServerRequestInterface $request)
{
    // Provide the Cloud Project ID by setting env var.
    $projectId = getenv('GOOGLE_PROJECT_ID');
    $datasetId = 'my_new_dataset_' . time();
    // Example large payload from BigQuery's public dataset.
    $query = 'SELECT abstract FROM `bigquery-public-data.breathe.bioasq` LIMIT 1000';

    $bigQuery = new BigQueryClient([
        'projectId' => $projectId,
        'datasetId' => $datasetId,
    ]);
    $jobConfig = $bigQuery->query($query);
    $job = $bigQuery->startQuery($jobConfig);

    $backoff = new ExponentialBackoff(10);
    $backoff->execute(function () use ($job) {
        $job->reload();
        if (!$job->isComplete()) {
            throw new Exception('Job has not yet completed', 500);
        }
    });
    $queryResults = $job->queryResults();

    // Stream out large payload by iterating rows and flushing output.
    $i = 0;
    foreach ($queryResults as $row) {
        foreach ($row as $column => $value) {
            printf('%s' . PHP_EOL, json_encode($value));
            flush();
        }
    }
    printf('Successfully streamed rows');
}
// [END functions_response_streaming]
