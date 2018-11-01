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


use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\ApiCore\ApiException;

$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';
$table_id = (isset($argv[3])) ? $argv[3] : 'quickstart-instance-table';

$tableAdminClient = new BigtableTableAdminClient();

$tableName = $tableAdminClient->tableName($project_id, $instance_id, $table_id);

// [START bigtable_delete_table]
// Delete the entire table

printf('Checking if table %s exists...' . PHP_EOL, $table_id);

try {
    printf('Table %s exists.' . PHP_EOL, $table_id);
    printf('Deleting %s table.' . PHP_EOL, $table_id);
    $tableAdminClient->deleteTable($tableName);
    printf('Deleted %s table.' . PHP_EOL, $table_id);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf('Table %s does not exists' . PHP_EOL, $table_id);
    }
}
// [END bigtable_delete_table]
