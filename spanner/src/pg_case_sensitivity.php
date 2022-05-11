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

// [START spanner_postgresql_case_sensitivity]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Create a table with case-sensitive and case-folded columns for
 * a Spanner PostgreSQL database
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $tableName The name of the table to create, defaults to Singers.
 */
function pg_case_sensitivity(string $instanceId, string $databaseId, string $tableName = 'Singers'): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $operation = $database->updateDdl(
        sprintf(
            '
            CREATE TABLE %s (
                -- SingerId will be folded to "singerid"
                SingerId  bigint NOT NULL PRIMARY KEY,
                -- FirstName and LastName are double-quoted and will therefore retain their
                -- mixed case and are case-sensitive. This means that any statement that
                -- references any of these columns must use double quotes.
                "FirstName" varchar(1024) NOT NULL,
                "LastName"  varchar(1024) NOT NULL
            )', $tableName)
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created %s table in database %s on instance %s' . PHP_EOL,
        $tableName, $databaseId, $instanceId);
}
// [END spanner_postgresql_case_sensitivity]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
