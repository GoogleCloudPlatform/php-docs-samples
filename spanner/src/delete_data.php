<?php
/**
 * Copyright 2020 Google LLC
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

// [START spanner_delete_data]
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\KeyRange;
use Google\Cloud\Core\Exception\GoogleException;

/**
 * Deletes sample data from the given database.
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @throws GoogleException
 */
function delete_data($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    // Delete individual rows
    $albumsToDelete = $spanner->keySet([
        'keys' => [[2, 1], [2, 3]]
    ]);
    $database->delete('Albums', $albumsToDelete);

    // Delete a range of rows where the column key is >=3 and <5
    // NOTE: A KeyRange must include a start and end.
    // NOTE: startType and endType both default to KeyRange::TYPE_OPEN.
    $singersRange = $spanner->keyRange([
        'startType' => KeyRange::TYPE_CLOSED,
        'start' => [3],
        'endType' => KeyRange::TYPE_OPEN,
        'end' => [5]
    ]);
    $singersToDelete = $spanner->keySet([
        'ranges' => [$singersRange]
    ]);
    $database->delete('Singers', $singersToDelete);

    // Delete remaining Singers rows, which will also delete the remaining
    // Albums rows because Albums was defined with ON DELETE CASCADE
    $remainingSingers = $spanner->keySet([
        'all' => true
    ]);
    $database->delete('Singers', $remainingSingers);

    print('Deleted data.' . PHP_EOL);
}
// [END spanner_delete_data]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
