<?php
/**
 * Copyright 2024 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_list_database_operations]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\ListDatabaseOperationsRequest;
use Google\Cloud\Spanner\Admin\Database\V1\OptimizeRestoredDatabaseMetadata;

/**
 * List all optimize restored database operations in an instance.
 * Example:
 * ```
 * list_database_operations($instanceId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 */
function list_database_operations(string $projectId, string $instanceId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $parent = DatabaseAdminClient::instanceName($projectId, $instanceId);

    $filter = '(metadata.@type:type.googleapis.com/' .
                'google.spanner.admin.database.v1.OptimizeRestoredDatabaseMetadata)';
    $operations = $databaseAdminClient->listDatabaseOperations(
        new ListDatabaseOperationsRequest([
            'parent' => $parent,
            'filter' => $filter
        ])
    );

    foreach ($operations->iterateAllElements() as $operation) {
        $obj = new OptimizeRestoredDatabaseMetadata();
        $meta = $operation->getMetadata()->unpack($obj);
        $progress = $meta->getProgress()->getProgressPercent();
        $dbName = basename($meta->getName());
        printf('Database %s restored from backup is %d%% optimized.' . PHP_EOL, $dbName, $progress);
    }
}
// [END spanner_list_database_operations]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
