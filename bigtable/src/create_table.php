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

namespace Google\Cloud\Samples\Bigtable;

// [START bigtable_create_table]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\ApiCore\ApiException;

/**
 * Create a new table in a Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance where you need the table to reside
 * @param string $tableId The ID of the table to be generated
 */
function create_table(
    string $projectId,
    string $instanceId,
    string $tableId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $tableAdminClient = new BigtableTableAdminClient();

    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
    $tableName = $tableAdminClient->tableName($projectId, $instanceId, $tableId);

    // Check whether table exists in an instance.
    // Create table if it does not exists.
    $table = new Table();
    printf('Creating a Table : %s' . PHP_EOL, $tableId);

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
            printf('Created table %s' . PHP_EOL, $tableId);
        } else {
            throw $e;
        }
    }
}
// [END bigtable_create_table]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
