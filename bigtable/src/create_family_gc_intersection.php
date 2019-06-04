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

// [START bigtable_create_family_gc_intersection]
use Google\Cloud\Bigtable\Admin\V2\GcRule\Intersection as GcRuleIntersection;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;
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
