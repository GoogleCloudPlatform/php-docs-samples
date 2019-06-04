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
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID [FAMILY_ID]" . PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $table_id) = $argv;
$family_id = isset($argv[4]) ? $argv[4] : 'cf3';

// [START bigtable_update_gc_rule]

use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $table_id = 'The Bigtable table ID';

$tableAdminClient = new BigtableTableAdminClient();

$tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);

$columnFamily1 = new ColumnFamily();
print('Updating column family cf3 GC rule...' . PHP_EOL);
$columnFamily1->setGcRule((new GcRule)->setMaxNumVersions(1));
// Update the column family cf1 to update the GC rule
$columnModification = new Modification();
$columnModification->setId('cf3');
$columnModification->setUpdate($columnFamily1);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

print('Print column family cf3 GC rule after update...' . PHP_EOL);
printf('Column Family: cf3');
printf('%s' . PHP_EOL, $columnFamily1->serializeToJsonString());
// [END bigtable_update_gc_rule]
