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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigquery/api/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 3) {
    return printf("Usage: php %s PROJECT_ID SQL_QUERY\n", __FILE__);
}
list($_, $projectId, $query) = $argv;

# [START bigquery_query_dry_run]
use Google\Cloud\BigQuery\BigQueryClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $query = 'SELECT id, view_count FROM `bigquery-public-data.stackoverflow.posts_questions`';
/**
 * Dry runs the given query
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 * @param string $query The query to be run. For eg: $query = 'SELECT id, view_count FROM `bigquery-public-data.stackoverflow.posts_questions`'
 */
function browse_table(string $projectId, string $query): void {

  // Construct a BigQuery client object.
  $bigQuery = new BigQueryClient([
    'projectId' => $projectId,
  ]);

  // Set job configs
  $jobConfig = $bigQuery->query($query);
  $jobConfig->useQueryCache(false);
  $jobConfig->dryRun(true);

  // Extract query results
  $queryJob = $bigQuery->startJob($jobConfig);
  $info = $queryJob->info();

  printf('This query will process %s bytes' . PHP_EOL, $info['statistics']['totalBytesProcessed']);
}
# [END bigquery_query_dry_run]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
