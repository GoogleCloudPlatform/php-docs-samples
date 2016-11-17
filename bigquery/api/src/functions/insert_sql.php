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

# [START insert_sql]
use Google\Cloud\ServiceBuilder;

/**
 * @param string $projectId The Google project ID.
 * @param string $datasetId The BigQuery dataset ID.
 * @param string $source    The path to the source file to import.
 */
function insert_sql($projectId, $datasetId, $source)
{
    // instantiate the bigquery table service
    $builder = new ServiceBuilder([
        'projectId' => $projectId,
    ]);
    $bigQuery = $builder->bigQuery();
    // create the import job
    $file = fopen($source, 'r');
    while ($line = fgets($file)) {
        if (0 !== strpos(trim($line), 'INSERT')) {
            continue;
        }
        $bigQuery->runQuery($line, [
            'useLegacySql' => false,
            'defaultDataset' => ['datasetId' => $datasetId],
        ]);
    }
    print('Data imported successfully' . PHP_EOL);
}
# [END insert_sql]
