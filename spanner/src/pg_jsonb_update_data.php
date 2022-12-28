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

// [START spanner_postgresql_jsonb_update_data]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Insert/update data in a JSONB column in a Postgres table.
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $tableName The table in which the data needs to be updated.
 */
function pg_jsonb_update_data(
    string $instanceId,
    string $databaseId,
    string $tableName = 'Venues'
): void {
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $database->insertOrUpdateBatch($tableName, [
        [
            'VenueId' => 1,
            'VenueDetails' => '{"rating": 9, "open": true}'
        ],
        [
            'VenueId' => 4,
            'VenueDetails' => '[
                {
                    "name": null,
                    "available": true
                },' .
                // PG JSONB sorts first by key length and then lexicographically with
                // equivalent key length and takes the last value in the case of duplicate keys
                '{
                    "name": "room 2",
                    "available": false,
                    "name": "room 3"
                },
                {
                    "main hall": {
                        "description": "this is the biggest space",
                        "size": 200
                    }
                }
            ]'
        ],
        [
            'VenueId' => 42,
            'VenueDetails' => $spanner->pgJsonb([
                'name' => null,
                'open' => [
                    'Monday' => true,
                    'Tuesday' => false
                ],
                'tags' => ['large', 'airy'],
            ])
        ]
    ]);

    print(sprintf('Inserted/updated 3 rows in table %s', $tableName) . PHP_EOL);
}
// [END spanner_postgresql_jsonb_update_data]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
