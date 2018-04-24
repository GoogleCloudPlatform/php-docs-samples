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

use Google\Cloud\BigQuery\BigQueryClient;

/**
 * @param string $projectId The Google project ID.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $source    The path to the source file to import.
 */
function insert_sql($projectId, $datasetId, $source)
{
    // instantiate the bigquery client
    $bigQuery = new BigQueryClient([
        'projectId' => $projectId,
    ]);
    $dataset = $bigQuery->dataset($datasetId);
    // run a sync query for each line of the import
    $file = fopen($source, 'r');
    while ($line = fgets($file)) {
        if (0 !== strpos(trim($line), 'INSERT')) {
            continue;
        }
        $queryConfig = $bigQuery->query($line)->defaultDataset($dataset);
        $bigQuery->runQuery($queryConfig);
    }
    print('Data imported successfully' . PHP_EOL);
}
