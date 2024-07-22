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

// [START spanner_create_database_with_version_retention_period]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\GetDatabaseRequest;

/**
 * Creates a database with data retention for Point In Time Restore.
 * Example:
 * ```
 * create_database_with_version_retention_period($projectId, $instanceId, $databaseId, $retentionPeriod);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $retentionPeriod The data retention period for the database.
 */
function create_database_with_version_retention_period(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $retentionPeriod
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $instance = $databaseAdminClient->instanceName($projectId, $instanceId);
    $databaseFullName = $databaseAdminClient->databaseName($projectId, $instanceId, $databaseId);

    $operation = $databaseAdminClient->createDatabase(
        new CreateDatabaseRequest([
            'parent' => $instance,
            'create_statement' => sprintf('CREATE DATABASE `%s`', $databaseId),
            'extra_statements' => [
                'CREATE TABLE Singers (' .
                'SingerId     INT64 NOT NULL,' .
                'FirstName    STRING(1024),' .
                'LastName     STRING(1024),' .
                'SingerInfo   BYTES(MAX)' .
                ') PRIMARY KEY (SingerId)',
                'CREATE TABLE Albums (' .
                    'SingerId     INT64 NOT NULL,' .
                    'AlbumId      INT64 NOT NULL,' .
                    'AlbumTitle   STRING(MAX)' .
                ') PRIMARY KEY (SingerId, AlbumId),' .
                'INTERLEAVE IN PARENT Singers ON DELETE CASCADE',
                "ALTER DATABASE `$databaseId` SET OPTIONS(version_retention_period='$retentionPeriod')"
            ]
        ])
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $request = new GetDatabaseRequest(['name' => $databaseFullName]);
    $databaseInfo = $databaseAdminClient->getDatabase($request);

    print(sprintf(
        'Database %s created with version retention period %s',
        $databaseInfo->getName(), $databaseInfo->getVersionRetentionPeriod()
    ) . PHP_EOL);
}
// [END spanner_create_database_with_version_retention_period]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
