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

// [START bigtable_create_family_gc_union]
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Union as GcRuleUnion;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Protobuf\Duration;

/**
 * Create a new column family with a union GC rule
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance where the table resides
 * @param string $tableId The ID of the table in which the rule needs to be created
 */
function create_family_gc_union(
    string $projectId,
    string $instanceId,
    string $tableId
): void {
    $tableAdminClient = new BigtableTableAdminClient();

    $tableName = $tableAdminClient->tableName($projectId, $instanceId, $tableId);

    print('Creating column family cf3 with union GC rule...' . PHP_EOL);
    // Create a column family with GC policy to drop data that matches
    // at least one condition.
    // Define a GC rule to drop cells older than 5 days or not the
    // most recent version

    $columnFamily3 = new ColumnFamily();

    $ruleUnion = new GcRuleUnion();
    $ruleUnionArray = [
        (new GcRule())->setMaxNumVersions(2),
        (new GcRule())->setMaxAge((new Duration())->setSeconds(3600 * 24 * 5))
    ];
    $ruleUnion->setRules($ruleUnionArray);
    $union = new GcRule();
    $union->setUnion($ruleUnion);

    $columnFamily3->setGCRule($union);

    $columnModification = new Modification();
    $columnModification->setId('cf3');
    $columnModification->setCreate($columnFamily3);
    $tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

    print('Created column family cf3 with Union GC rule.' . PHP_EOL);
}
// [END bigtable_create_family_gc_union]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
