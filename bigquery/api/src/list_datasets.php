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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/bigquery/api/README.md
 */

namespace Google\Cloud\Samples\BigQuery;

# [START bigquery_list_datasets]
use Google\Cloud\BigQuery\BigQueryClient;

/**
 * List all datasets in the given project
 *
 * @param string $projectId The project Id of your Google Cloud Project.
 */
function list_datasets(string $projectId): void
{
    $bigQuery = new BigQueryClient([
      'projectId' => $projectId,
    ]);
    $datasets = $bigQuery->datasets();
    foreach ($datasets as $dataset) {
        print($dataset->id() . PHP_EOL);
    }
}
# [END bigquery_list_datasets]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
