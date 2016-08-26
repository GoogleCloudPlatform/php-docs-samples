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

# [START browse_table]
use Google\Cloud\ServiceBuilder;

/**
 * Browse a bigquery table.
 * Example:
 * ```
 * browse_table($projectId, $datasetId, $tableId);
 * ```
 *
 * @param string $projectId  The Google project ID.
 * @param string $datasetId  The BigQuery dataset ID.
 * @param string $tableId    The BigQuery table ID.
 * @param string $maxResults The number of results to return at a time.
 * @param string $startIndex The row index to start on.
 *
 * @return int number of rows returned
 */
function browse_table($projectId, $datasetId, $tableId, $maxResults = 10, $startIndex = 0)
{
    $options = [
        'maxResults' => $maxResults,
        'startIndex' => $startIndex
    ];
    $builder = new ServiceBuilder([
        'projectId' => $projectId,
    ]);
    $bigQuery = $builder->bigQuery();
    $dataset = $bigQuery->dataset($datasetId);
    $table = $dataset->table($tableId);
    $numRows = 0;
    foreach ($table->rows($options) as $row) {
        print('---');
        foreach ($row as $column => $value) {
            printf('%s: %s' . PHP_EOL, $column, $value);
        }
        $numRows++;
    }

    return $numRows;
}
# [END browse_table]

# [START paginate_table]
/**
 * Paginate through a bigquery table.
 * Example:
 * ```
 * $shouldPaginateFunc = function () {
 *     return true; // always paginate
 * }
 * browse_table($projectId, $datasetId, $tableId);
 * ```
 *
 * @param string   $projectId          The Google project ID.
 * @param string   $datasetId          The BigQuery dataset ID.
 * @param string   $tableId            The BigQuery table ID.
 * @param string   $maxResults         The number of results to return at a time.
 * @param callable $shouldPaginateFunc function to determine if pagination should continue.
 */
function paginate_table($projectId, $datasetId, $tableId, $maxResults = 10, $shouldPaginateFunc = null)
{
    if (is_null($shouldPaginateFunc)) {
        $shouldPaginateFunc = function () {
            return true;
        };
    }
    $totalRows = 0;
    do {
        $rows = browse_table($projectId, $datasetId, $tableId, $maxResults, $totalRows);
        $totalRows += $rows;
    } while ($rows && 0 === $totalRows % $maxResults && $shouldPaginateFunc());

    return $totalRows;
}
# [END paginate_table]
