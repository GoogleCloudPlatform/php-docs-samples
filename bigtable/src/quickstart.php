<?php

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

// Include Google Cloud dependencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 3 || count($argv) > 5) {
    return printf('Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID [LOCATION_ID]' . PHP_EOL, __FILE__);
}
list($_, $projectId, $instanceId, $tableId) = $argv;
$locationId = isset($argv[5]) ? $argv[5] : 'us-east1-b';

// [START bigtable_quickstart]
use Google\Cloud\Bigtable\BigtableClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $instanceId = 'The Bigtable instance ID';
// $tableId = 'The Bigtable table ID';

// Connect to an existing table with an existing instance.
$dataClient = new BigtableClient([
    'projectId' => $projectId,
]);
$table = $dataClient->table($instanceId, $tableId);
$key = 'r1';
// Read a row from my-table using a row key
$row = $table->readRow($key);

$columnFamilyId = 'cf1';
$columnId = 'c1';
// Get the Value from the Row, using the column_family_id and column_id
$value = $row[$columnFamilyId][$columnId][0]['value'];

printf("Row key: %s\nData: %s\n", $key, $value);
// [END bigtable_quickstart]
