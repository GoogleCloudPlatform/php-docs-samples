<?php

use Psr\Http\Message\ServerRequestInterface;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Core\ExponentialBackoff;

function streamBigQuery(ServerRequestInterface $request)
{
    $projectId = 'YOUR_PROJECT_ID';
    $query = 'SELECT abstract FROM `bigquery-public-data.breathe.bioasq` LIMIT 1000';

    $bigQuery = new BigQueryClient([
        'projectId' => $projectId,
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

    $i = 0;
    foreach ($queryResults as $row) {
        foreach ($row as $column => $value) {
            printf('%s' . PHP_EOL, json_encode($value));
            flush();
        }
    }
}