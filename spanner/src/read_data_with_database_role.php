<?php
/**
 * Copyright 2023 Google Inc.
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

// [START spanner_read_data_with_database_role]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Read database with a database role.
 * Example:
 * ```
 * read_data_with_database_role($instanceId, $databaseId, $databaseRole);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $databaseRole The database role.
 */
function read_data_with_database_role(string $instanceId, string $databaseId, string $databaseRole
): void {
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId, ['databaseRole' => $databaseRole]);
    $results = $database->execute('SELECT * FROM Singers');

    foreach ($results as $row) {
        printf('SingerId: %s, Firstname: %s, LastName: %s' . PHP_EOL, $row['SingerId'], $row['FirstName'], $row['LastName']);
    }
}
// [END spanner_read_data_with_database_role]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
