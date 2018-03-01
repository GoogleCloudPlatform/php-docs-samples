<?php
/**
 * Copyright 2016 Google Inc.
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

// [START spanner_query_data_with_new_column]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Queries sample data from the database using SQL.
 * This sample uses the `MarketingBudget` column. You can add the column
 * by running the `add_column` sample or by running this DDL statement against
 * your database:
 *
 *      ALTER TABLE Albums ADD COLUMN MarketingBudget INT64
 *
 * Example:
 * ```
 * query_data_with_new_column($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function query_data_with_new_column($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $results = $database->execute(
        'SELECT SingerId, AlbumId, MarketingBudget FROM Albums'
    );

    foreach ($results as $row) {
        printf('SingerId: %s, AlbumId: %s, MarketingBudget: %d' . PHP_EOL,
            $row['SingerId'], $row['AlbumId'], $row['MarketingBudget']);
    }
}
// [END spanner_query_data_with_new_column]
