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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/README.md
 */

namespace Google\Cloud\Samples\Bigtable;

// [START bigtable_insert_update_rows]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\Table as TableClass;
use Google\ApiCore\ApiException;

/**
 * Perform insert/update operations on a Bigtable
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $tableId The ID of the table on which we intend to insert/update rows
 */
function insert_update_rows(
    string $projectId,
    string $instanceId = 'quickstart-instance-php',
    string $tableId = 'bigtable-php-table'
): void {
    $dataClient = new BigtableClient([
        'projectId' => $projectId,
    ]);

    $instanceAdminClient = new BigtableInstanceAdminClient();
    $tableAdminClient = new BigtableTableAdminClient();

    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
    $tableName = $tableAdminClient->tableName($projectId, $instanceId, $tableId);

    $table = new TableClass();

    printf('Creating table %s' . PHP_EOL, $tableId);

    try {
        $tableAdminClient->createtable(
            $instanceName,
            $tableId,
            $table
        );
    } catch (ApiException $e) {
        if ($e->getStatus() === 'ALREADY_EXISTS') {
            printf('Table %s already exists.' . PHP_EOL, $tableId);
            return;
        }
        throw $e;
    }

    printf('Table %s created' . PHP_EOL, $tableId);

    $table = $dataClient->table($instanceId, $tableId);
    $columnFamilyId = 'cf1';

    printf('Creating column family %s' . PHP_EOL, $columnFamilyId);

    $columnFamily4 = new ColumnFamily();
    $columnModification = new Modification();
    $columnModification->setId($columnFamilyId);
    $columnModification->setCreate($columnFamily4);
    $tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

    printf('Inserting data in the table' . PHP_EOL);

    $insertRows = [
        'rk5' => [
            'cf1' => [
                'cq5' => [
                    'value' => 'Value5',
                    'timeStamp' => time_in_microseconds()
                ]
            ]
        ]
    ];
    $table->upsert($insertRows);

    printf('Data inserted successfully!' . PHP_EOL);
}

function time_in_microseconds()
{
    $mt = microtime(true);
    $mt = sprintf('%.03f', $mt);
    return (float) $mt * 1000000;
}
// [END bigtable_insert_update_rows]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
