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

// [START spanner_postgresql_interleaved_table]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateDatabaseDdlRequest;

/**
 * Create an interleaved table on a Spanner PostgreSQL database.
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $parentTable The parent table to create. Defaults to 'Singers'
 * @param string $childTable The child table to create. Defaults to 'Albums'
 */
function pg_interleaved_table(string $projectId, string $instanceId, string $databaseId, string $parentTable = 'Singers', string $childTable = 'Albums'): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);

    // The Spanner PostgreSQL dialect extends the PostgreSQL dialect with certain Spanner
    // specific features, such as interleaved tables.
    // See https://cloud.google.com/spanner/docs/postgresql/data-definition-language#create_table
    // for the full CREATE TABLE syntax.

    $parentTableQuery = sprintf('CREATE TABLE %s (
        SingerId  bigint NOT NULL PRIMARY KEY,
        FirstName varchar(1024) NOT NULL,
        LastName  varchar(1024) NOT NULL
    )', $parentTable);

    $childTableQuery = sprintf('CREATE TABLE %s (
        SingerId bigint NOT NULL,
        AlbumId  bigint NOT NULL,
        Title    varchar(1024) NOT NULL,
        PRIMARY KEY (SingerId, AlbumId)
    ) INTERLEAVE IN PARENT %s ON DELETE CASCADE', $childTable, $parentTable);

    $request = new UpdateDatabaseDdlRequest([
        'database' => $databaseName,
        'statements' => [$parentTableQuery, $childTableQuery]
    ]);

    $operation = $databaseAdminClient->updateDatabaseDdl($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created interleaved table hierarchy using PostgreSQL dialect' . PHP_EOL);
}
// [END spanner_postgresql_interleaved_table]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
