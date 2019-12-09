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
require_once __DIR__ . '/../../vendor/autoload.php';

if (count($argv) < 3 || count($argv) > 5) {
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID" . PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $table_id) = $argv;

// [START bigtable_writes_batch]

use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\Bigtable\Mutations;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $table_id = 'mobile-time-series';

// Connect to an existing table with an existing instance.
$dataClient = new BigtableClient([
    'projectId' => $project_id,
]);
$table = $dataClient->table($instance_id, $table_id);

$timestampMicros = time() * 1000 * 1000;
$columnFamilyId = 'stats_summary';
$mutations = [
    (new Mutations())
        ->upsert($columnFamilyId, "connected_wifi", 1, $timestampMicros)
        ->upsert($columnFamilyId, "os_build", "12155.0.0-rc1", $timestampMicros),
    (new Mutations())
        ->upsert($columnFamilyId, "connected_wifi", 1, $timestampMicros)
        ->upsert($columnFamilyId, "os_build", "12145.0.0-rc6", $timestampMicros)];

$table->mutateRows([
    "tablet#a0b81f74#20190501" => $mutations[0],
    "tablet#a0b81f74#20190502" => $mutations[1]
]);

printf('Successfully wrote 2 rows.' . PHP_EOL);
// [END bigtable_writes_batch]
