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


use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Union as GcRuleUnion;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Intersection as GcRuleIntersection;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;

use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\ApiCore\ApiException;
use Google\Protobuf\Duration;

$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';
$table_id = (isset($argv[3])) ? $argv[3] : 'quickstart-instance-table';
/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $cluster_id = 'The Bigtable table ID';
// $location_id = 'The Bigtable region ID';

$instanceAdminClient = new BigtableInstanceAdminClient();
$tableAdminClient = new BigtableTableAdminClient();

$instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);
$tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);

// Check whether table exists in an instance.
// Create table if it does not exists.
$table = new Table();
printf('Checking if table %s exists' . PHP_EOL, $table_id);

try {
    $tableAdminClient->getTable($tableName, ['view' => View::NAME_ONLY]);
    printf('Table %s alredy exists' . PHP_EOL, $table_id);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf('Creating the %s table' . PHP_EOL, $table_id);

        $tableAdminClient->createtable(
            $instanceName,
            $table_id,
            $table
        );
        printf('Created table %s' . PHP_EOL, $table_id);
    }
}
// [START bigtable_list_tables]
$tables = iterator_to_array($tableAdminClient->listTables($instanceName)->iterateAllElements());
if ($tables != []) {
    foreach ($tables as $table) {
        print($table->getName() . PHP_EOL);
    }
} else {
    print('No table exists in current project...' . PHP_EOL);
}
// [END bigtable_list_tables]

// [START bigtable_create_family_gc_max_age]
print('Creating column family cf1 with MaxAge GC Rule...' . PHP_EOL);
// Create a column family with GC policy : maximum age
// where age = current time minus cell timestamp

$columnFamily1 = new ColumnFamily();
$duration = new Duration();
$duration->setSeconds(3600 * 24 * 5);
$MaxAgeRule = (new GcRule)->setMaxAge($duration);
$columnFamily1->setGcRule($MaxAgeRule);

$columnModification = new Modification();
$columnModification->setId('cf1');
$columnModification->setCreate($columnFamily1);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);
print('Created column family cf1 with MaxAge GC Rule.' . PHP_EOL);

// [END bigtable_list_tables]

// [START bigtable_create_family_gc_max_age]

print('Creating column family cf2 with max versions GC rule...' . PHP_EOL);
$columnFamily2 = new ColumnFamily();
$maxVersionRule = (new GcRule)->setMaxNumVersions(2);
$columnFamily2->setGCRule($maxVersionRule);

$columnModification = new Modification();
$columnModification->setId('cf2');
$columnModification->setCreate($columnFamily2);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

print('Created column family cf2 with Max Versions GC Rule.' . PHP_EOL);

// [END bigtable_create_family_gc_max_versions]

// [START bigtable_create_family_gc_union]

print('Creating column family cf3 with union GC rule...' . PHP_EOL);
// Create a column family with GC policy to drop data that matches
// at least one condition.
// Define a GC rule to drop cells older than 5 days or not the
// most recent version
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
$columnModification->setId('cf3');
$columnModification->setCreate($columnFamily3);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

print('Created column family cf3 with Union GC rule' . PHP_EOL);

// [END bigtable_create_family_gc_union]

// [START bigtable_create_family_gc_intersection]

print('Creating column family cf4 with Intersection GC rule...' . PHP_EOL);
$columnFamily4 = new ColumnFamily();

$intersection_rule = new GcRuleIntersection();
$intersection_array = [
    (new GcRule)->setMaxAge((new Duration())->setSeconds(3600 * 24 * 5)),
    (new GcRule)->setMaxNumVersions(2)
];
$intersection_rule->setRules($intersection_array);

$intersection = new GcRule();
$intersection->setIntersection($intersection_rule);

$columnFamily4->setGCRule($intersection);

$columnModification = new Modification();
$columnModification->setId('cf4');
$columnModification->setCreate($columnFamily4);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);

print('Created column family cf4 with Union GC rule' . PHP_EOL);

// [END bigtable_create_family_gc_intersection]

// [START bigtable_create_family_gc_nested]

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

// [START bigtable_list_column_families]
$table = $tableAdminClient->getTable($tableName);
$columnFamilies = iterator_to_array($table->getColumnFamilies()->getIterator());
foreach ($columnFamilies as $k => $columnFamily) {
    printf('Column Family: %s' . PHP_EOL, $k);
    print('GC Rule:' . PHP_EOL);
    printf('%s' . PHP_EOL, $columnFamily->serializeToJsonString());
}
// [END bigtable_list_column_families]
print('Print column family cf1 GC rule before update...' . PHP_EOL);
print('Column Family: cf1' . PHP_EOL);
print($columnFamily1->serializeToJsonString() . PHP_EOL);
// [START bigtable_update_gc_rule]
print('Updating column family cf1 GC rule...' . PHP_EOL);
$columnFamily1->setGcRule((new GcRule)->setMaxNumVersions(1));
// Update the column family cf1 to update the GC rule
$columnModification = new Modification();
$columnModification->setId('cf1');
$columnModification->setUpdate($columnFamily1);

print('Print column family cf1 GC rule after update...' . PHP_EOL);
print('Column Family: cf1');
printf('%s' . PHP_EOL, $columnFamily1->serializeToJsonString());
// [END bigtable_update_gc_rule]

// [START bigtable_delete_family]
print('Delete a column family cf2...' . PHP_EOL);
// Delete a column family
$columnModification = new Modification();
$columnModification->setId('cf2');
$columnModification->setDrop(true);
$tableAdminClient->modifyColumnFamilies($tableName, [$columnModification]);
print('Column family cf2 deleted successfully.' . PHP_EOL);
// [END bigtable_delete_family]
