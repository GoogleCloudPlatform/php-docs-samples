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

# [START all]
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\BigQuery\BigQueryClient;

// get the project ID as the first argument
if (2 != count($argv) || !$projectId = $argv[1]) {
    die("Usage: php shakespeare.php YOUR_PROJECT_ID\n");
}

# [START build_service]
$bigQuery = new BigQueryClient([
    'projectId' => $projectId,
]);
# [END build_service]
# [START run_query]
$query = 'SELECT TOP(corpus, 10) as title, COUNT(*) as unique_words ' .
         'FROM [publicdata:samples.shakespeare]';
$options = ['useLegacySql' => true];
$queryResults = $bigQuery->runQuery($query, $options);
# [END run_query]

# [START print_results]
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
# [END print_results]
# [END all]
