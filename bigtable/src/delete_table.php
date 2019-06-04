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

// [START bigtable_delete_table]

use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $table_id = 'The Bigtable table ID';

$tableAdminClient = new BigtableTableAdminClient();

$tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);

// Delete the entire table

try {
    printf('Attempting to delete table %s.' . PHP_EOL, $table_id);
    $tableAdminClient->deleteTable($tableName);
    printf('Deleted %s table.' . PHP_EOL, $table_id);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf('Table %s does not exists' . PHP_EOL, $table_id);
    } else {
        throw $e;
    }
}
// [END bigtable_delete_table]
