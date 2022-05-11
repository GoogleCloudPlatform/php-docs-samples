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

// [START spanner_postgresql_numeric_data_type]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Transaction;
use Google\Cloud\Spanner\Database;

/**
 * Shows how to work with the PostgreSQL NUMERIC/DECIMAL data type on a Spanner PostgreSQL database.
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $tableName The table name in which the numeric column will reside.
 */
function pg_numeric_data_type(string $instanceId, string $databaseId, string $tableName = 'Venues'): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    // Create a table that includes a column with data type NUMERIC. As the database has been
    // created with the PostgreSQL dialect, the data type that is used will be the PostgreSQL
    // NUMERIC data type.
    $operation = $database->updateDdl(
        sprintf('CREATE TABLE %s (
            VenueId  bigint NOT NULL PRIMARY KEY,
            Name     varchar(1024) NOT NULL,
            Revenues numeric
        )', $tableName)
    );

    print('Creating the table...' . PHP_EOL);
    $operation->pollUntilComplete();

    $sql = sprintf('INSERT INTO %s (VenueId, Name, Revenues)'
    . ' VALUES ($1, $2, $3)', $tableName);

    $database->runTransaction(function (Transaction $t) use ($spanner, $sql) {
        $count = $t->executeUpdate($sql, [
                'parameters' => [
                    'p1' => 1,
                    'p2' => 'Venue 1',
                    'p3' => $spanner->pgNumeric('3150.25')
                ]
            ]);
        $t->commit();

        printf('Inserted %d venue(s).' . PHP_EOL, $count);
    });

    $database->runTransaction(function (Transaction $t) use ($spanner, $sql) {
        $count = $t->executeUpdate($sql, [
                'parameters' => [
                    'p1' => 2,
                    'p2' => 'Venue 2',
                    'p3' => null
                ],
                // we have to supply the type of the parameter which is null
                'types' => [
                    'p3' => Database::TYPE_PG_NUMERIC
                ]
            ]);
        $t->commit();

        printf('Inserted %d venue(s) with NULL revenue.' . PHP_EOL, $count);
    });

    $database->runTransaction(function (Transaction $t) use ($spanner, $sql) {
        $count = $t->executeUpdate($sql, [
                'parameters' => [
                    'p1' => 3,
                    'p2' => 'Venue 4',
                    'p3' => $spanner->pgNumeric('NaN')
                ]
            ]);
        $t->commit();

        printf('Inserted %d venue(s) with NaN revenue.' . PHP_EOL, $count);
    });
}
// [END spanner_postgresql_numeric_data_type]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
