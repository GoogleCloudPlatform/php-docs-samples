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

// [START spanner_write_data_for_struct_queries]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Inserts sample data that can be used to test STRUCT parameters in queries.
 *
 * The database and table must already exist and can be created using
 * `create_database`.
 * Example:
 * ```
 * insert_struct_data($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function insert_struct_data($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $operation = $database->transaction(['singleUse' => true])
        ->insertBatch('Singers', [
            ['SingerId' => 6, 'FirstName' => 'Elena', 'LastName' => 'Campbell'],
            ['SingerId' => 7, 'FirstName' => 'Gabriel', 'LastName' => 'Wright'],
            ['SingerId' => 8, 'FirstName' => 'Benjamin', 'LastName' => 'Martinez'],
            ['SingerId' => 9, 'FirstName' => 'Hannah', 'LastName' => 'Harris'],
        ])
        ->commit();

    print('Inserted data.' . PHP_EOL);
}
// [END spanner_write_data_for_struct_queries]
