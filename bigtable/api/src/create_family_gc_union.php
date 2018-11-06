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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/api/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 3 || count($argv) > 5) {
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID [FAMILY_ID]" . PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $table_id) = $argv;
$family_id = isset($argv[4]) ? $argv[4] : 'cf3';

// [START bigtable_create_family_gc_union]

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Union as GcRuleUnion;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Protobuf\Duration;
use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $table_id = 'The Bigtable table ID';
// $location_id = 'The Bigtable region ID';

$instanceAdminClient = new BigtableInstanceAdminClient();
$tableAdminClient = new BigtableTableAdminClient();

$instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);
$tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);


printf('Creating column family %s with union GC rule...' . PHP_EOL, $family_id);
// Create a column family with GC policy to drop data that matches
// at least one condition.
// Define a GC rule to drop cells older than 5 days or not the
// most recent version

try {
    $tableAdminClient->getTable($tableName, ['view' => View::NAME_ONLY]);
    printf('Table %s exists' . PHP_EOL, $table_id);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf('Table %s doesn\'t exists' . PHP_EOL, $table_id);
        return;
    }
}


$columnFamily3 = new ColumnFamily();

$rule_union = new GcRuleUnion();
$rule_union_array = [
    (new GcRule)->setMaxNumVersions(2),
    (new GcRule)->setMaxAge((new Duration())->setSeconds(3600 * 24 * 5))
];
$rule_union->setRules($rule_union_array);
$union = new GcRule();
$union->setUnion($rule_union);

$columnFamily3->setGCRule($union);

$columnModification = new Modification();
$columnModification->setId($family_id);
$columnModification->setCreate($columnFamily3);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

printf('Created column family %s with Union GC rule.' . PHP_EOL, $family_id);

// [END bigtable_create_family_gc_union]