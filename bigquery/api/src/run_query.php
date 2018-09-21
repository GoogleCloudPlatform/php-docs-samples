<?php
/**
 * Copyright 2018 Google LLC.
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

use Google\Cloud\BigQuery\BigQueryClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $query = 'SELECT id, view_count FROM `bigquery-public-data.stackoverflow.posts_questions`';

$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
$jobConfig = $bigQuery->query($query);
$queryResults = $bigQuery->runQuery($jobConfig);

$i = 0;
foreach ($queryResults as $row) {
    printf('--- Row %s ---' . PHP_EOL, ++$i);
    foreach ($row as $column => $value) {
        printf('%s: %s' . PHP_EOL, $column, json_encode($value));
    }
}
printf('Found %s row(s)' . PHP_EOL, $i);
