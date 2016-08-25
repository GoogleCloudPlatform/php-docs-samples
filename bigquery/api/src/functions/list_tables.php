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

# [START list_tables]
use Google\Cloud\ServiceBuilder;

/**
 * @param string $projectId The Google project ID.
 * @param string $datasetId The BigQuery dataset ID.
 */
function list_tables($projectId, $datasetId)
{
    $builder = new ServiceBuilder([
        'projectId' => $projectId,
    ]);
    $bigQuery = $builder->bigQuery();
    $dataset = $bigQuery->dataset($datasetId);
    $tables = $dataset->tables();
    foreach ($tables as $table) {
        print($table->id() . PHP_EOL);
    }
}

/**
 * @param string $projectId The Google project ID.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $tableId   The BigQuery table ID.
 */
function get_table($projectId, $datasetId, $tableId)
{
    $builder = new ServiceBuilder([
        'projectId' => $projectId,
    ]);
    $bigQuery = $builder->bigQuery();
    $dataset = $bigQuery->dataset($datasetId);
    return $dataset->table($tableId);
}
# [END list_tables]
