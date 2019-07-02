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

// [START spanner_create_table_with_datatypes]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Creates a table with suported datatypes.
 * Example:
 * ```
 * create_table_with_datatypes($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function create_table_with_datatypes($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    
    $operation = $database->updateDdl(
        "CREATE TABLE Venues (
            VenueId		           INT64 NOT NULL,
            VenueName              STRING(100),
            VenueInfo              BYTES(MAX),
            Capacity               INT64,
            AvailableDates         ARRAY<DATE>,
            LastContactDate        DATE,
            OutdoorVenue           BOOL,
            PopularityScore        FLOAT64,
            LastUpdateTime TIMESTAMP NOT NULL OPTIONS (allow_commit_timestamp=true)
    	) PRIMARY KEY (VenueId)"
    );
    
    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created Venues table in database %s on instance %s' . PHP_EOL,
        $databaseId, $instanceId);
}
// [END spanner_create_table_with_datatypes]
