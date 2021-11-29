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

// [START bigtable_list_tables]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

/**
 * List tables in an instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 */
function list_tables(
    string $projectId,
    string $instanceId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $tableAdminClient = new BigtableTableAdminClient();

    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    printf('Listing Tables:' . PHP_EOL);
    $tables = $tableAdminClient->listTables($instanceName)->iterateAllElements();
    if (empty($tables)) {
        print('No table exists.' . PHP_EOL);
        return;
    }
    foreach ($tables as $table) {
        print($table->getName() . PHP_EOL);
    }
}
// [END bigtable_list_tables]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
