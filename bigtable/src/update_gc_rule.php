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

// [START bigtable_update_gc_rule]
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\ApiCore\ApiException;

/**
 * Update the GC Rule for an existing column family in the table
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $tableId The ID of the table where the rule needs to be updated
 * @param string $familyId The ID of the column family
 */
function update_gc_rule(
    string $projectId,
    string $instanceId,
    string $tableId,
    string $familyId = 'cf3'
): void {
    $tableAdminClient = new BigtableTableAdminClient();
    $tableName = $tableAdminClient->tableName($projectId, $instanceId, $tableId);
    $columnFamily1 = new ColumnFamily();

    printf('Updating column family %s GC rule...' . PHP_EOL, $familyId);
    $columnFamily1->setGcRule((new GcRule())->setMaxNumVersions(1));
    // Update the column family with ID $familyId to update the GC rule
    $columnModification = new Modification();
    $columnModification->setId($familyId);
    $columnModification->setUpdate($columnFamily1);

    try {
        $tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Column family %s does not exist.' . PHP_EOL, $familyId);
            return;
        }
        throw $e;
    }

    printf('Print column family %s GC rule after update...' . PHP_EOL, $familyId);
    printf('Column Family: ' . $familyId . PHP_EOL);
    printf('%s' . PHP_EOL, $columnFamily1->serializeToJsonString());
}
// [END bigtable_update_gc_rule]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
