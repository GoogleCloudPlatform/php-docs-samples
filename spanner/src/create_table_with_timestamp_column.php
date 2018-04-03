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

// [START spanner_create_table_with_timestamp_column]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Creates a table with a commit timestamp column.
 * Example:
 * ```
 * create_table_with_timestamp_column($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function create_table_with_timestamp_column($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    
    $operation = $database->updateDdl(
        "CREATE TABLE Performances (
    		SingerId	INT64 NOT NULL,
    		VenueId		INT64 NOT NULL,
    		EventDate	DATE,
    		Revenue		INT64,
    		LastUpdateTime	TIMESTAMP NOT NULL OPTIONS (allow_commit_timestamp=true)
    	) PRIMARY KEY (SingerId, VenueId, EventDate),
    	INTERLEAVE IN PARENT Singers on DELETE CASCADE"
    );
    
    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created Performances table in database %s on instance %s' . PHP_EOL,
        $databaseId, $instanceId);
}
// [END spanner_create_table_with_timestamp_column]
