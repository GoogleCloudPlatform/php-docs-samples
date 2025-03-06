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

// [START spanner_postgres_create_database]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\DatabaseDialect;
use Google\Cloud\Spanner\Admin\Database\V1\GetDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseDdlRequest;

/**
 * Creates a database that uses Postgres dialect
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_create_database(string $projectId, string $instanceId, string $databaseId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $instance = $databaseAdminClient->instanceName($projectId, $instanceId);
    $databaseName = $databaseAdminClient->databaseName($projectId, $instanceId, $databaseId);

    $table1Query = 'CREATE TABLE Singers (
        SingerId   bigint NOT NULL PRIMARY KEY,
        FirstName  varchar(1024),
        LastName   varchar(1024),
        SingerInfo bytea,
        FullName character varying(2048) GENERATED
        ALWAYS AS (FirstName || \' \' || LastName) STORED
    )';
    $table2Query = 'CREATE TABLE Albums (
        AlbumId      bigint NOT NULL,
        SingerId     bigint NOT NULL REFERENCES Singers (SingerId),
        AlbumTitle   text,
        PRIMARY KEY(SingerId, AlbumId)
    )';

    $operation = $databaseAdminClient->createDatabase(
        new CreateDatabaseRequest([
            'parent' => $instance,
            'create_statement' => sprintf('CREATE DATABASE "%s"', $databaseId),
            'extra_statements' => [],
            'database_dialect' => DatabaseDialect::POSTGRESQL
        ])
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $request = new UpdateDatabaseDdlRequest([
        'database' => $databaseName,
        'statements' => [$table1Query, $table2Query]
    ]);

    $operation = $databaseAdminClient->updateDatabaseDdl($request);
    $operation->pollUntilComplete();

    $database = $databaseAdminClient->getDatabase(
        new GetDatabaseRequest(['name' => $databaseAdminClient->databaseName($projectId, $instanceId, $databaseId)])
    );
    $dialect = DatabaseDialect::name($database->getDatabaseDialect());

    printf('Created database %s with dialect %s on instance %s' . PHP_EOL,
        $databaseId, $dialect, $instanceId);
}
// [END spanner_postgres_create_database]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
