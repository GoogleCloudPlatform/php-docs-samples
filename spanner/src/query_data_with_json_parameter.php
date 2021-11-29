<?php
/**
 * Copyright 2021 Google LLC.
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

// [START spanner_query_with_json_parameter]
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\SpannerClient;

/**
 * Queries sample data from the database using SQL with a NUMERIC parameter.
 * Example:
 * ```
 * query_data_with_json_parameter($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function query_data_with_json_parameter($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $exampleJson = [
        'rating' => 9,
        'open' => true,
    ];

    $results = $database->execute(
        'SELECT VenueId, VenueDetails FROM Venues ' .
        'WHERE JSON_VALUE(VenueDetails, \'$.rating\') = JSON_VALUE(@venueDetails, \'$.rating\')',
        [
            'parameters' => [
                'venueDetails' => json_encode($exampleJson)
            ],
            'types' => [
                'venueDetails' => Database::TYPE_JSON
            ]
        ]
    );

    foreach ($results as $row) {
        printf(
            'VenueId: %s, VenueDetails: %s' . PHP_EOL,
            $row['VenueId'],
            $row['VenueDetails']
        );
    }
}
// [END spanner_query_with_json_parameter]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
