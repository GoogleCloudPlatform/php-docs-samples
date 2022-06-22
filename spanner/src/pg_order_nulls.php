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

// [START spanner_postgresql_order_nulls]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Shows how a Spanner PostgreSQL database orders null values in a
 * query, and how an application can change the default behavior by adding
 * `NULLS FIRST` or `NULLS LAST` to an `ORDER BY` clause.
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $tableName The table to create. Defaults to 'Singers'
 */
function pg_order_nulls(string $instanceId, string $databaseId, string $tableName = 'Singers'): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $query = sprintf('CREATE TABLE %s (
        SingerId  bigint NOT NULL PRIMARY KEY,
        Name varchar(1024)
    )', $tableName);

    $operation = $database->updateDdl($query);

    print('Creating the table...' . PHP_EOL);
    $operation->pollUntilComplete();
    print('Singers table created...' . PHP_EOL);

    $database->insertOrUpdateBatch($tableName, [
        [
            'SingerId' => 1,
            'Name' => 'Bruce'
        ],
        [
            'SingerId' => 2,
            'Name' => 'Alice'
        ],
        [
            'SingerId' => 3,
            'Name' => null
        ]
    ]);

    print('Added 3 singers' . PHP_EOL);

    // Spanner PostgreSQL follows the ORDER BY rules for NULL values of PostgreSQL. This means that:
    // 1. NULL values are ordered last by default when a query result is ordered in ascending order.
    // 2. NULL values are ordered first by default when a query result is ordered in descending order.
    // 3. NULL values can be order first or last by specifying NULLS FIRST or NULLS LAST in the ORDER BY clause.
    $results = $database->execute(sprintf('SELECT * FROM %s ORDER BY Name', $tableName));
    print_results($results);

    $results = $database->execute(sprintf('SELECT * FROM %s ORDER BY Name DESC', $tableName));
    print_results($results);

    $results = $database->execute(sprintf('SELECT * FROM %s ORDER BY Name NULLS FIRST', $tableName));
    print_results($results);

    $results = $database->execute(sprintf('SELECT * FROM %s ORDER BY Name DESC NULLS LAST', $tableName));
    print_results($results);
}

// helper function to print data
function print_results($results): void
{
    foreach ($results as $row) {
        printf('SingerId: %s, Name: %s' . PHP_EOL, $row['singerid'], $row['name'] ?? 'NULL');
    }
}
// [END spanner_postgresql_order_nulls]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
