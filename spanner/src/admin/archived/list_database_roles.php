<?php
/**
 * Copyright 2023 Google Inc.
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

// [START spanner_list_database_roles]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\ListDatabaseRolesRequest;

/**
 * List Database roles in the given database.
 * Example:
 * ```
 * list_database_roles($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google cloud project ID
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function list_database_roles(
    string $projectId,
    string $instanceId,
    string $databaseId
): void {
    $adminClient = new DatabaseAdminClient();
    $resource = $adminClient->databaseName($projectId, $instanceId, $databaseId);
    $listDatabaseRolesRequest = (new ListDatabaseRolesRequest())
        ->setParent($resource);

    $roles = $adminClient->listDatabaseRoles($listDatabaseRolesRequest);
    printf('List of Database roles:' . PHP_EOL);
    foreach ($roles as $role) {
        printf($role->getName() . PHP_EOL);
    }
}
// [END spanner_list_database_roles]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
