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

// [START spanner_update_database]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\Database;
use Google\Cloud\Spanner\Admin\Database\V1\GetDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseRequest;
use Google\Protobuf\FieldMask;

/**
 * Updates the drop protection setting for a database.
 * Example:
 * ```
 * update_database($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function update_database(string $projectId, string $instanceId, string $databaseId): void
{
    $newUpdateMaskField = new FieldMask([
        'paths' => ['enable_drop_protection']
    ]);
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseFullName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    $database = (new Database())
        ->setEnableDropProtection(true)
        ->setName($databaseFullName);

    printf('Updating database %s', $databaseId);
    $operation = $databaseAdminClient->updateDatabase((new UpdateDatabaseRequest())
        ->setDatabase($database)
        ->setUpdateMask($newUpdateMaskField));

    $operation->pollUntilComplete();

    $database = $databaseAdminClient->getDatabase(
        new GetDatabaseRequest(['name' => $databaseFullName])
    );
    printf(
        'Updated the drop protection for %s to %s' . PHP_EOL,
        $database->getName(),
        $database->getEnableDropProtection()
    );
}
// [END spanner_update_database]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
