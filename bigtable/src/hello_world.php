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

if (count($argv) != 4) {
    return printf('Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID' . PHP_EOL, __FILE__);
}
list($_, $projectId, $instanceId, $tableId) = $argv;

// [START bigtable_hw_imports]
use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\Bigtable\Mutations;
use Google\Cloud\Bigtable\V2\RowFilter;

// [END bigtable_hw_imports]

// [START bigtable_hw_connect]
/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $instanceId = 'The Bigtable instance ID';
// $tableId = 'The Bigtable table ID';

$instanceAdminClient = new BigtableInstanceAdminClient();
$tableAdminClient = new BigtableTableAdminClient();
$dataClient = new BigtableClient([
    'projectId' => $projectId,
]);
// [END bigtable_hw_connect]

// [START bigtable_hw_create_table]
$instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
$tableName = $tableAdminClient->tableName($projectId, $instanceId, $tableId);

// Check whether table exists in an instance.
// Create table if it does not exists.
$table = new Table();
printf('Creating a Table: %s' . PHP_EOL, $tableId);

try {
    $tableAdminClient->getTable($tableName, ['view' => View::NAME_ONLY]);
    printf('Table %s already exists' . PHP_EOL, $tableId);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf('Creating the %s table' . PHP_EOL, $tableId);

        $tableAdminClient->createtable(
            $instanceName,
            $tableId,
            $table
        );
        $columnFamily = new ColumnFamily();
        $columnModification = new Modification();
        $columnModification->setId('cf1');
        $columnModification->setCreate($columnFamily);
        $tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);
        printf('Created table %s' . PHP_EOL, $tableId);
    } else {
        throw $e;
    }
}
// [END bigtable_hw_create_table]

// [START bigtable_hw_write_rows]
$table = $dataClient->table($instanceId, $tableId);

printf('Writing some greetings to the table.' . PHP_EOL);
$greetings = ['Hello World!', 'Hello Cloud Bigtable!', 'Hello PHP!'];
$entries = [];
$columnFamilyId = 'cf1';
$column = 'greeting';
foreach ($greetings as $i => $value) {
    $rowKey = sprintf('greeting%s', $i);
    $rowMutation = new Mutations();
    $rowMutation->upsert($columnFamilyId, $column, $value, time() * 1000 * 1000);
    $entries[$rowKey] = $rowMutation;
}
$table->mutateRows($entries);
// [END bigtable_hw_write_rows]

// [START bigtable_hw_get_with_filter]
printf('Getting a single greeting by row key.' . PHP_EOL);
$key = 'greeting0';
// Only retrieve the most recent version of the cell.
$rowFilter = (new RowFilter())->setCellsPerColumnLimitFilter(1);

$column = 'greeting';
$columnFamilyId = 'cf1';

$row = $table->readRow($key, [
    'rowFilter' => $rowFilter
]);
printf('%s' . PHP_EOL, $row[$columnFamilyId][$column][0]['value']);
// [END bigtable_hw_get_with_filter]

// [START bigtable_hw_scan_all]
$columnFamilyId = 'cf1';
$column = 'greeting';
printf('Scanning for all greetings:' . PHP_EOL);
$partialRows = $table->readRows([])->readAll();
foreach ($partialRows as $row) {
    printf('%s' . PHP_EOL, $row[$columnFamilyId][$column][0]['value']);
}
// [END bigtable_hw_scan_all]

// [START bigtable_hw_delete_table]
try {
    printf('Attempting to delete table %s.' . PHP_EOL, $tableId);
    $tableAdminClient->deleteTable($tableName);
    printf('Deleted %s table.' . PHP_EOL, $tableId);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf('Table %s does not exists' . PHP_EOL, $tableId);
    } else {
        throw $e;
    }
}
// [END bigtable_hw_delete_table]
