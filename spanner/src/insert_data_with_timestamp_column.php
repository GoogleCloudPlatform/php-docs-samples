<?php
/**
 * Copyright 2018 Google Inc.
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

// [START spanner_insert_data_with_timestamp_column]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Inserts sample data into a table with a commit timestamp column.
 *
 * The database and table must already exist and can be created using
 * `create_table_with_timestamp_column`.
 * Example:
 * ```
 * insert_data_with_timestamp_column($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function insert_data_with_timestamp_column($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $operation = $database->transaction(['singleUse' => true])
        ->insertBatch('Performances', [
            ['SingerId' => 1, 'VenueId' => 4, 'EventDate' => '2017-10-05', 'Revenue' => 11000, 'LastUpdateTime' => $spanner->commitTimestamp()],
            ['SingerId' => 1, 'VenueId' => 19, 'EventDate' => '2017-11-02', 'Revenue' => 15000, 'LastUpdateTime' => $spanner->commitTimestamp()],
            ['SingerId' => 2, 'VenueId' => 42, 'EventDate' => '2017-12-23', 'Revenue' => 7000, 'LastUpdateTime' => $spanner->commitTimestamp()],
        ])
        ->commit();

    print('Inserted data.' . PHP_EOL);
}
// [END spanner_insert_data_with_timestamp_column]
