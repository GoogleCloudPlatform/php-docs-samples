<?php
/**
 * Copyright 2019 Google LLC.
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

// [START spanner_query_with_array_parameter]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\Date;

/**
 * Queries sample data from the database using SQL with an ARRAY parameter.
 * Example:
 * ```
 * query_data_with_array_parameter($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function query_data_with_array_parameter($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $exampleArray = [
        new Date(new \DateTime('2020-10-01')),
        new Date(new \DateTime('2020-11-01'))
    ];
   
    $results = $database->execute(
        'SELECT VenueId, VenueName, AvailableDate FROM Venues v, ' .
        'UNNEST(v.AvailableDates) as AvailableDate ' .
        'WHERE AvailableDate in UNNEST(@availableDates)',
        [
            'parameters' => [
                'availableDates' => $exampleArray
            ]
        ]
    );

    foreach ($results as $row) {
        printf('VenueId: %s, VenueName: %s, AvailableDate: %s' . PHP_EOL,
            $row['VenueId'], $row['VenueName'], $row['AvailableDate']);
    }
}
// [END spanner_query_with_array_parameter]
