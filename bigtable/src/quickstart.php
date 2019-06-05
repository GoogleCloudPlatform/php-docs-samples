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
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID [LOCATION_ID]" . PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $table_id) = $argv;
$location_id = isset($argv[5]) ? $argv[5] : 'us-east1-b';

// [START bigtable_quickstart]

use Google\Cloud\Bigtable\BigtableClient;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $table_id = 'The Bigtable table ID';


// Connect to an existing table with an existing instance.
$dataClient = new BigtableClient([
    'projectId' => $project_id,
]);
$table = $dataClient->table($instance_id, $table_id);
$key = 'r1';
// Read a row from my-table using a row key
$row = $table->readRow($key);

$column_family_id = 'cf1';
$column_id = 'c1';
// Get the Value from the Row, using the column_family_id and column_id

$value = $row[$column_family_id][$column_id][0]['value'];

printf("Row key: %s\nData: %s\n", $key, $value);
// [END bigtable_quickstart]
