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
 *
 * For more information on this function:
 *
 * @see https://cloud.google.com/spanner/docs/secondary-indexes#storing_clause
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_read_data_with_storing_index]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Reads sample data from the database using an index with a storing
 * clause.
 *
 * The index must exist before running this sample. You can add the index
 * by running the `add_storing_index` sample or by running this DDL statement
 * against your database:
 *
 *     CREATE INDEX AlbumsByAlbumTitle2 ON Albums(AlbumTitle)
 *     STORING (MarketingBudget)
 *
 * Example:
 * ```
 * read_data_with_storing_index($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function read_data_with_storing_index(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $keySet = $spanner->keySet(['all' => true]);
    $results = $database->read(
        'Albums',
        $keySet,
        ['AlbumId', 'AlbumTitle', 'MarketingBudget'],
        ['index' => 'AlbumsByAlbumTitle2']
    );

    foreach ($results->rows() as $row) {
        printf('AlbumId: %s, AlbumTitle: %s, MarketingBudget: %d' . PHP_EOL,
            $row['AlbumId'], $row['AlbumTitle'], $row['MarketingBudget']);
    }
}
// [END spanner_read_data_with_storing_index]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
