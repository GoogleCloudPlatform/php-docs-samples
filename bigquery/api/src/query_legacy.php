<?php
/**
 * Copyright 2022 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\BigQuery;

// [START bigquery_query_legacy]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Query using legacy sql
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 */
function query_legacy(string $projectId): void
{
    $query = 'SELECT corpus FROM [bigquery-public-data:samples.shakespeare] GROUP BY corpus';

    $bigQuery = new BigQueryClient([
      'projectId' => $projectId,
    ]);
    $jobConfig = $bigQuery->query($query)->useLegacySql(true);

    $queryResults = $bigQuery->runQuery($jobConfig);

    $i = 0;
    foreach ($queryResults as $row) {
        printf('--- Row %s ---' . PHP_EOL, ++$i);
        foreach ($row as $column => $value) {
            printf('%s: %s' . PHP_EOL, $column, json_encode($value));
        }
    }
    printf('Found %s row(s)' . PHP_EOL, $i);
}
// [END bigquery_query_legacy]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
