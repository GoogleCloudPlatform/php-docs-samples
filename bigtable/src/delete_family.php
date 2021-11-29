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

// [START bigtable_delete_family]
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

/**
 * Delete a column family in a table
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $tableId The ID of the table where the column family needs to be deleted
 * @param string $familyId The ID of the column family to be deleted
 */
function delete_family(
    string $projectId,
    string $instanceId,
    string $tableId,
    string $familyId = 'cf2'
): void {
    $tableAdminClient = new BigtableTableAdminClient();
    $tableName = $tableAdminClient->tableName($projectId, $instanceId, $tableId);

    print("Delete a column family $familyId..." . PHP_EOL);
    // Delete a column family
    $columnModification = new Modification();
    $columnModification->setId($familyId);
    $columnModification->setDrop(true);
    $tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);
    print("Column family $familyId deleted successfully." . PHP_EOL);
}
// [END bigtable_delete_family]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
