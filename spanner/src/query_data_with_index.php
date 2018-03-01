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

// [START spanner_query_data_with_index]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Queries sample data from the database using SQL and an index.
 *
 * The index must exist before running this sample. You can add the index
 * by running the `add_index` sample or by running this DDL statement against
 * your database:
 *
 *     CREATE INDEX AlbumsByAlbumTitle ON Albums(AlbumTitle)
 *
 * Example:
 * ```
 * query_data_with_index($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $startTitle The start of the title index.
 * @param string $endTitle   The end of the title index.
 */
function query_data_with_index($instanceId, $databaseId, $startTitle, $endTitle)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $parameters = [
        'startTitle' => $startTitle,
        'endTitle' => $endTitle
    ];

    $results = $database->execute(
        'SELECT AlbumId, AlbumTitle, MarketingBudget ' .
        'FROM Albums@{FORCE_INDEX=AlbumsByAlbumTitle} ' .
        'WHERE AlbumTitle >= @startTitle AND AlbumTitle < @endTitle',
        ['parameters' => $parameters]
    );

    foreach ($results as $row) {
        printf('AlbumId: %s, AlbumTitle: %s, MarketingBudget: %d' . PHP_EOL,
            $row['AlbumId'], $row['AlbumTitle'], $row['MarketingBudget']);
    }
}
// [END spanner_query_data_with_index]
