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

// [START spanner_insert_data]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Inserts sample data into the given database.
 *
 * The database and table must already exist and can be created using
 * `create_database`.
 * Example:
 * ```
 * insert_data($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function insert_data($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $operation = $database->transaction(['singleUse' => true])
        ->insertBatch('Singers', [
            ['SingerId' => 1, 'FirstName' => 'Marc', 'LastName' => 'Richards'],
            ['SingerId' => 2, 'FirstName' => 'Catalina', 'LastName' => 'Smith'],
            ['SingerId' => 3, 'FirstName' => 'Alice', 'LastName' => 'Trentor'],
            ['SingerId' => 4, 'FirstName' => 'Lea', 'LastName' => 'Martin'],
            ['SingerId' => 5, 'FirstName' => 'David', 'LastName' => 'Lomond'],
        ])
        ->insertBatch('Albums', [
            ['SingerId' => 1, 'AlbumId' => 1, 'AlbumTitle' => 'Total Junk'],
            ['SingerId' => 1, 'AlbumId' => 2, 'AlbumTitle' => 'Go, Go, Go'],
            ['SingerId' => 2, 'AlbumId' => 1, 'AlbumTitle' => 'Green'],
            ['SingerId' => 2, 'AlbumId' => 2, 'AlbumTitle' => 'Forever Hold Your Peace'],
            ['SingerId' => 2, 'AlbumId' => 3, 'AlbumTitle' => 'Terrified']
        ])
        ->commit();

    print('Inserted data.' . PHP_EOL);
}
// [END spanner_insert_data]
