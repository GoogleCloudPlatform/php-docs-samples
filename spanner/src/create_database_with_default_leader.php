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

// [START spanner_create_database_with_default_leader]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\Database;
use Google\Cloud\Spanner\Admin\Database\V1\GetDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseRequest;

/**
 * Creates a database with a default leader.
 * Example:
 * ```
 * create_database_with_default_leader($instanceId, $databaseId, $defaultLeader);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $defaultLeader The leader instance configuration used by default.
 */
function create_database_with_default_leader(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $defaultLeader
): void {
    $databaseAdminClient = new DatabaseAdminClient();

    $instance = $databaseAdminClient->instanceName($projectId, $instanceId);
    $databaseIdFull = $databaseAdminClient->databaseName($projectId, $instanceId, $databaseId);

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
                'INTERLEAVE IN PARENT Singers ON DELETE CASCADE'
            ]
        ])
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $database = (new Database())
        ->setDefaultLeader($defaultLeader)
        ->setName($databaseIdFull);

    printf('Updating database %s', $databaseId);
    $operation = $databaseAdminClient->updateDatabase((new UpdateDatabaseRequest())
        ->setDatabase($database));

    $database = $databaseAdminClient->getDatabase(
        new GetDatabaseRequest(['name' => $databaseIdFull])
    );
    printf('Created database %s on instance %s with default leader %s' . PHP_EOL,
        $databaseId, $instanceId, $database->getDefaultLeader());
}
// [END spanner_create_database_with_default_leader]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
