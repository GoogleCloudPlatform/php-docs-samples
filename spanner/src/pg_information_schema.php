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

// [START spanner_postgresql_information_schema]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Query the information schema metadata in a Spanner PostgreSQL database
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_information_schema(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $operation = $database->updateDdl(
        '
        CREATE TABLE Venues (
            VenueId  bigint NOT NULL PRIMARY KEY,
            Name     varchar(1024) NOT NULL,
            Revenues numeric,
            Picture  bytea
        )'
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    // The Spanner INFORMATION_SCHEMA tables can be used to query the metadata of tables and
    // columns of PostgreSQL databases. The returned results will include additional PostgreSQL
    // metadata columns.

    // Get all the user tables in the database. PostgreSQL uses the `public` schema for user
    // tables. The table_catalog is equal to the database name.

    $results = $database->execute(
        '
        SELECT table_catalog, table_schema, table_name,
            user_defined_type_catalog,
            user_defined_type_schema,
            user_defined_type_name
        FROM INFORMATION_SCHEMA.tables
        WHERE table_schema=\'public\'
        ');

    printf('Details fetched.' . PHP_EOL);
    foreach ($results as $row) {
        foreach ($row as $key => $val) {
            printf('%s: %s' . PHP_EOL, $key, $val);
        }
    }
}
// [END spanner_postgresql_information_schema]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
