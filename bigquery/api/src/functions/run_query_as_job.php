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
# [START query_as_job]
use Google\Cloud\ServiceBuilder;
use Google\Cloud\ExponentialBackoff;

/**
 * Run a BigQuery query as a job.
 * Example:
 * ```
 * $query = 'SELECT TOP(corpus, 10) as title, COUNT(*) as unique_words ' .
 *          'FROM [publicdata:samples.shakespeare]';
 * run_query_as_job($projectId, $query, true);
 * ```.
 *
 * @param string $projectId The Google project ID.
 * @param string $query     A SQL query to run. *
 * @param bool $useLegacySql Specifies whether to use BigQuery's legacy SQL
 *        syntax or standard SQL syntax for this query.
 */
function run_query_as_job($projectId, $query, $useLegacySql)
{
    $builder = new ServiceBuilder([
        'projectId' => $projectId,
    ]);
    $bigQuery = $builder->bigQuery();
    $job = $bigQuery->runQueryAsJob(
        $query,
        ['jobConfig' => ['useLegacySql' => $useLegacySql]]);
    $backoff = new ExponentialBackoff(10);
    $backoff->execute(function () use ($job) {
        print('Waiting for job to complete' . PHP_EOL);
        $job->reload();
        if (!$job->isComplete()) {
            throw new Exception('Job has not yet completed', 500);
        }
    });
    $queryResults = $job->queryResults();

    if ($queryResults->isComplete()) {
        $i = 0;
        $rows = $queryResults->rows();
        foreach ($rows as $row) {
            printf('--- Row %s ---' . PHP_EOL, ++$i);
            foreach ($row as $column => $value) {
                printf('%s: %s' . PHP_EOL, $column, $value);
            }
        }
        printf('Found %s row(s)' . PHP_EOL, $i);
    } else {
        throw new Exception('The query failed to complete');
    }
}
# [END query_as_job]
