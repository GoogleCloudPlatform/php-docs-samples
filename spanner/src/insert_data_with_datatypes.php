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

// [START spanner_insert_datatypes_data]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Inserts sample data into a table with supported datatypes.
 *
 * The database and table must already exist and can be created using
 * `create_table_with_datatypes`.
 * Example:
 * ```
 * insert_data_with_datatypes($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function insert_data_with_datatypes($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $operation = $database->transaction(['singleUse' => true])
        ->insertBatch('Venues', [
            [
                'VenueId' => 4,
                'VenueName' => 'Venue 4',
                'VenueInfo' => base64_encode('Hello World 1'),
                'Capacity' => 1800,
                'AvailableDates' => ['2020-12-01', '2020-12-02', '2020-12-03'],
                'LastContactDate' => '2018-09-02',
                'OutdoorVenue' => false,
                'PopularityScore' => 0.85543,
                'LastUpdateTime' => $spanner->commitTimestamp()
            ], [
                'VenueId' => 19,
                'VenueName' => 'Venue 19',
                'VenueInfo' => base64_encode('Hello World 2'),
                'Capacity' => 6300,
                'AvailableDates' => ['2020-11-01', '2020-11-05', '2020-11-15'],
                'LastContactDate' => '2019-01-15',
                'OutdoorVenue' => true,
                'PopularityScore' => 0.98716,
                'LastUpdateTime' => $spanner->commitTimestamp()
            ], [
                'VenueId' => 42,
                'VenueName' => 'Venue 42',
                'VenueInfo' => base64_encode('Hello World 3'),
                'Capacity' => 3000,
                'AvailableDates' => ['2020-10-01', '2020-10-07'],
                'LastContactDate' => '2018-10-01',
                'OutdoorVenue' => false,
                'PopularityScore' => 0.72598,
                'LastUpdateTime' => $spanner->commitTimestamp()
            ],
        ])
        ->commit();

    print('Inserted data.' . PHP_EOL);
}
// [END spanner_insert_datatypes_data]
