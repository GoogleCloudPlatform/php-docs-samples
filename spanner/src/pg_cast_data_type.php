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

// [START spanner_postgresql_cast_data_type]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Cast values from one data type to another in a Spanner PostgreSQL SQL statement
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function pg_cast_data_type(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $sql = "select 1::varchar as str, '2'::int as int, 3::decimal as dec,
            '4'::bytea as bytes, 5::float as float, 'true'::bool as bool,
            '2021-11-03T09:35:01UTC'::timestamptz as timestamp";

    $results = $database->execute($sql);

    foreach ($results as $row) {
        printf('String: %s' . PHP_EOL, $row['str']);
        printf('Int: %d' . PHP_EOL, $row['int']);
        printf('Decimal: %s' . PHP_EOL, $row['dec']);
        printf('Bytes: %s' . PHP_EOL, $row['bytes']);
        printf('Float: %f' . PHP_EOL, $row['float']);
        printf('Bool: %s' . PHP_EOL, $row['bool']);
        printf('Timestamp: %s' . PHP_EOL, (string) $row['timestamp']);
    }
}
// [END spanner_postgresql_cast_data_type]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
