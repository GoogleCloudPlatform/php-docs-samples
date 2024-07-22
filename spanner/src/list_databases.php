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

// [START spanner_list_databases]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\ListDatabasesRequest;

/**
 * Lists the databases and their leader options.
 * Example:
 * ```
 * list_databases($projectId, $instanceId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 */
function list_databases(string $projectId, string $instanceId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $instanceName = DatabaseAdminClient::instanceName($projectId, $instanceId);

    $request = new ListDatabasesRequest(['parent' => $instanceName]);
    $resp = $databaseAdminClient->listDatabases($request);
    $databases = $resp->iterateAllElements();
    printf('Databases for %s' . PHP_EOL, $instanceName);
    foreach ($databases as $database) {
        printf("\t%s (default leader = %s)" . PHP_EOL, $database->getName(), $database->getDefaultLeader());
    }
}
// [END spanner_list_databases]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
