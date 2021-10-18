<?php
/**
 * Copyright 2020 Google LLC.
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

// [START spanner_create_client_with_query_options]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Database;

/**
 * Create a client with query options.
 * Example:
 * ```
 * create_client_with_query_options($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function create_client_with_query_options($instanceId, $databaseId)
{
    $spanner = new SpannerClient([
        'queryOptions' => [
            'optimizerVersion' => '1',
            // Pin the statistics package used for this client instance to the
            // latest version. The list of available statistics packages can be
            // found by querying the "INFORMATION_SCHEMA.SPANNER_STATISTICS"
            // table.
            'optimizerStatisticsPackage' => 'latest'
        ]
    ]);
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $results = $database->execute(
        'SELECT VenueId, VenueName, LastUpdateTime FROM Venues'
    );

    foreach ($results as $row) {
        printf('VenueId: %s, VenueName: %s, LastUpdateTime: %s' . PHP_EOL,
            $row['VenueId'], $row['VenueName'], $row['LastUpdateTime']);
    }
}
// [END spanner_create_client_with_query_options]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
