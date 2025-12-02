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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_postgresql_jsonb_query_parameter]

use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\SpannerClient;

/**
 * Query data to a jsonb column in a PostgreSQL table.
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $tableName The table from which the data needs to be queried.
 */
function pg_jsonb_query_parameter(
    string $instanceId,
    string $databaseId,
    string $tableName = 'Venues'
): void {
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $results = $database->execute(
        sprintf('SELECT venueid, venuedetails FROM %s', $tableName) .
        " WHERE CAST(venuedetails ->> 'rating' AS INTEGER) > $1",
        [
        'parameters' => [
            'p1' => 2
        ],
        'types' => [
            'p1' => Database::TYPE_INT64
        ]
        ]
    );

    foreach ($results as $row) {
        printf('VenueId: %s, VenueDetails: %s' . PHP_EOL, $row['venueid'], $row['venuedetails']);
    }
}
// [END spanner_postgresql_jsonb_query_parameter]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
