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

# [START bigquery_simple_app_all]
require __DIR__ . '/vendor/autoload.php';

# [START bigquery_simple_app_deps]
use Google\Cloud\BigQuery\BigQueryClient;

# [END bigquery_simple_app_deps]

// get the project ID as the first argument
if (2 != count($argv)) {
    die("Usage: php stackoverflow.php YOUR_PROJECT_ID\n");
}

$projectId = $argv[1];

# [START bigquery_simple_app_client]
$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
# [END bigquery_simple_app_client]
# [START bigquery_simple_app_query]
$query = <<<ENDSQL
SELECT
  CONCAT(
    'https://stackoverflow.com/questions/',
    CAST(id as STRING)) as url,
  view_count
FROM `bigquery-public-data.stackoverflow.posts_questions`
WHERE tags like '%google-bigquery%'
ORDER BY view_count DESC
LIMIT 10;
ENDSQL;
$queryJobConfig = $bigQuery->query($query);
$queryResults = $bigQuery->runQuery($queryJobConfig);
# [END bigquery_simple_app_query]

# [START bigquery_simple_app_print]
if ($queryResults->isComplete()) {
    $i = 0;
    $rows = $queryResults->rows();
    foreach ($rows as $row) {
        printf('--- Row %s ---' . PHP_EOL, ++$i);
        printf('url: %s, %s views' . PHP_EOL, $row['url'], $row['view_count']);
    }
    printf('Found %s row(s)' . PHP_EOL, $i);
} else {
    throw new Exception('The query failed to complete');
}
# [END bigquery_simple_app_print]
# [END bigquery_simple_app_all]
