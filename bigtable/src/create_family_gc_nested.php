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
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID" . PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $table_id) = $argv;

// [START bigtable_create_family_gc_nested]

use Google\Cloud\Bigtable\Admin\V2\GcRule\Intersection as GcRuleIntersection;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Union as GcRuleUnion;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Protobuf\Duration;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $table_id = 'The Bigtable table ID';

$tableAdminClient = new BigtableTableAdminClient();

$tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);


print('Creating column family cf5 with a Nested GC rule...' . PHP_EOL);
// Create a column family with nested GC policies.
// Create a nested GC rule:
// Drop cells that are either older than the 10 recent versions
// OR
// Drop cells that are older than a month AND older than the
// 2 recent versions
$columnFamily5 = new ColumnFamily();
$rule1 = (new GcRule)->setMaxNumVersions(10);

$rule2Intersection = new GcRuleIntersection();
$rule2Duration1 = new Duration();
$rule2Duration1->setSeconds(3600 * 24 * 30);
$rule2Array = [
    (new GcRule)->setMaxAge($rule2Duration1),
    (new GcRule)->setMaxNumVersions(2)
];
$rule2Intersection->setRules($rule2Array);
$rule2 = new GcRule();
$rule2->setIntersection($rule2Intersection);

$nested_rule = new GcRuleUnion();
$nested_rule->setRules([
    $rule1,
    $rule2
]);
$nested_rule = (new GcRule())->setUnion($nested_rule);

$columnFamily5->setGCRule($nested_rule);

$columnModification = new Modification();
$columnModification->setId('cf5');
$columnModification->setCreate($columnFamily5);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

print('Created column family cf5 with a Nested GC rule.' . PHP_EOL);

// [END bigtable_create_family_gc_nested]
