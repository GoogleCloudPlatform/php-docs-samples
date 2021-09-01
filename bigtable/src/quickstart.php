<?php

namespace Google\Cloud\Samples\Bigtable;

/**
 * Copyright 2019 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/README.md
 */

// [START bigtable_quickstart]
use Google\Cloud\Bigtable\BigtableClient;

/**
 * The quickstart sample
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $tableId The ID of the table to be created
 */
function quickstart(
    string $projectId,
    string $instanceId,
    string $tableId
): void {
    // Connect to an existing table with an existing instance.
    $dataClient = new BigtableClient([
        'projectId' => $projectId,
    ]);
    $table = $dataClient->table($instanceId, $tableId);

    $key = 'r1';
    $columnFamilyId = 'cf1';
    $columnId = 'c1';

    // Read a row from my-table using a row key
    $row = $table->readRow($key);

    // Get the Value from the Row, using the column_family_id and column_id
    $value = $row[$columnFamilyId][$columnId][0]['value'];

    printf('Row key: %s' . PHP_EOL . 'Data: %s' . PHP_EOL, $key, $value);
}
// [END bigtable_quickstart]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
