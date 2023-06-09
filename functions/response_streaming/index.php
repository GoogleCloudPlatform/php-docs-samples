<?php

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
