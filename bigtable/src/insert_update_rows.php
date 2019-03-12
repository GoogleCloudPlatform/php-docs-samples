<?php
/**
 * Copyright 2018 Google Inc.
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
require __DIR__ . '/../vendor/autoload.php';

$instance_id = 'quickstart-instance-php'; # instance-id
$table_id    = 'bigtable-php-table'; # my-table
$project_id  = getenv('PROJECT_ID');

// [START bigtable_insert_update_rows]

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\Table as TableClass;

$dataClient = new BigtableClient([
    'projectId' => $project_id,
]);

$instanceAdminClient = new BigtableInstanceAdminClient();
$tableAdminClient = new BigtableTableAdminClient();

$instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);
$tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);

$table = new TableClass();

$tableAdminClient->createtable(
    $instanceName,
    $table_id,
    $table
);

$table = $dataClient->table($instance_id, $table_id);

$columnFamily4 = new ColumnFamily();
$columnModification = new Modification();
$columnModification->setId('cf1');
$columnModification->setCreate($columnFamily4);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

function time_in_microseconds()
{
    $mt = microtime(true);
    $mt = sprintf('%.03f', $mt);
    return (float)$mt*1000000;
}
$insertRows = [
    'rk5' => [
        'cf1' => [
            'cq5' => [
                'value' => "Value5",
                'timeStamp' => time_in_microseconds()
            ]
        ]
    ]
];
$table->upsert($insertRows);
// [END bigtable_insert_update_rows]
