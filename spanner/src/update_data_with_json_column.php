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

// [START spanner_update_data_with_json_column]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Updates sample data in a table with a JSON column.
 *
 * Before executing this method, a new column Revenue has to be added to the Venues
 * table by applying the DDL statement "ALTER TABLE Venues ADD COLUMN VenueDetails JSON".
 *
 * Example:
 * ```
 * update_data_with_json_column($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function update_data_with_json_column($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->transaction(['singleUse' => true])
        ->updateBatch('Venues', [
            [
                'VenueId' => 4,
                'VenueDetails' =>
                    '[{"name":"room 1","open":true},{"name":"room 2","open":false}]'
            ],
            [
                'VenueId' => 19,
                'VenueDetails' => '{"rating":9,"open":true}'
            ],
            [
                'VenueId' => 42,
                'VenueDetails' =>
                    '{"name":null,"open":{"Monday":true,"Tuesday":false},"tags":["large","airy"]}'
            ],
        ])
        ->commit();

    print('Updated data.' . PHP_EOL);
}
// [END spanner_update_data_with_json_column]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
