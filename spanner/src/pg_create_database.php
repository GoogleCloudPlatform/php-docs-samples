<?php
/**
 * Copyright 2022 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_create_postgres_database]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Admin\Database\V1\DatabaseDialect;

/**
 * Creates a database that uses Postgres dialect
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_create_database(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);

    if (!$instance->exists()) {
        throw new \LogicException("Instance $instanceId does not exist");
    }

    // A DB with PostgreSQL dialect does not support extra DDL statements in the
    // `createDatabase` call.
    $operation = $instance->createDatabase($databaseId, [
        'databaseDialect' => DatabaseDialect::POSTGRESQL
    ]);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $database = $instance->database($databaseId);
    $dialect = DatabaseDialect::name($database->info()['databaseDialect']);

    printf('Created database %s with dialect %s on instance %s' . PHP_EOL,
        $databaseId, $dialect, $instanceId);

    $table1Query = 'CREATE TABLE Singers (
        SingerId   bigint NOT NULL PRIMARY KEY,
        FirstName  varchar(1024),
        LastName   varchar(1024),
        SingerInfo bytea
    )';

    $table2Query = 'CREATE TABLE Albums (
        AlbumId      bigint NOT NULL,
        SingerId     bigint NOT NULL REFERENCES Singers (SingerId),
        AlbumTitle   text,
        PRIMARY KEY(SingerId, AlbumId)
    )';

    // You can execute the DDL queries in a call to updateDdl/updateDdlBatch
    $operation = $database->updateDdlBatch([$table1Query, $table2Query]);
    $operation->pollUntilComplete();
}
// [END spanner_create_postgres_database]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
