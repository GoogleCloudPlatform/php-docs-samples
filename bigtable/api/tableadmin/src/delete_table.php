<?php

/**
 * Copyright 2018 Google LLC.
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

/*
 * Demonstrates how to connect to Cloud Bigtable and run some basic operations.
 *
 * Prerequisites:
 *
 * - Create a Cloud Bigtable cluster.
 *   https://cloud.google.com/bigtable/docs/creating-cluster
 * - Set your Google Application Default Credentials.
 *   https://developers.google.com/identity/protocols/application-default-credentials
 *
 * Operations performed:
 * - Delete a Bigtable table.
 */

require __DIR__ . '/vendor/autoload.php';


use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Union as GcRuleUnion;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Intersection as GcRuleIntersection;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;

use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\ApiCore\ApiException;

use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;

use Google\Protobuf\Duration;


function delete_table($project_id, $instance_id, $table_id)
{
    /**
     * Check Instance exists.
     * * Creates a Production instance with default Cluster.
     * * List instances in a project.
     *   List clusters in an instance.
     *
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     */

    $tableAdminClient = new BigtableTableAdminClient();

    $formattedTable = $tableAdminClient->tableName($project_id, $instance_id, $table_id);

    // [START bigtable_delete_table]
    // Delete the entire table

    printf('Checking if table %s exists...' . PHP_EOL, $table_id);

    try {
        printf('Table %s exists.' . PHP_EOL, $table_id);
        printf('Deleting %s table.' . PHP_EOL, $table_id);
        $tableAdminClient->deleteTable($formattedTable);
        printf('Deleted %s table.' . PHP_EOL, $table_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Table %s does not exists' . PHP_EOL, $table_id);
        }
    }
    // [END bigtable_delete_table]
}


$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';
$table_id = (isset($argv[3])) ? $argv[3] : 'quickstart-instance-table';

delete_table($project_id, $instance_id, $table_id);