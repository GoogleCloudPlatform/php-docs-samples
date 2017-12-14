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

# [START all]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * Run a BigQuery query.
 * Example:
 * ```
 * $query = 'SELECT id, view_count FROM `bigquery-public-data.stackoverflow.posts_questions`' .
 *          'WHERE tags like \'%google-bigquery%\' ORDER BY view_count DESC';
 * run_query($projectId, $query, true);
 * ```.
 *
 * @param string $projectId The Google project ID.
 * @param string $query     A SQL query to run.
 * @param bool $useLegacySql Specifies whether to use BigQuery's legacy SQL
 *        syntax or standard SQL syntax for this query.
 */
function run_query($projectId, $query, $useLegacySql)
{
    # [START build_service]
    $bigQuery = new BigQueryClient([
        'projectId' => $projectId,
    ]);
    # [END build_service]
    # [START run_query]
    $jobConfig = $bigQuery->query($query)->useLegacySql($useLegacySql);
    $queryResults = $bigQuery->runQuery($jobConfig);
    # [END run_query]

    # [START print_results]
    $i = 0;
    foreach ($queryResults as $row) {
        printf('--- Row %s ---' . PHP_EOL, ++$i);
        foreach ($row as $column => $value) {
            printf('%s: %s' . PHP_EOL, $column, $value);
        }
    }
    printf('Found %s row(s)' . PHP_EOL, $i);
    # [END print_results]
}
# [END all]
