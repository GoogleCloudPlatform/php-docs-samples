<?php
/**
 * Copyright 2025 Google Inc.
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

use Google\Cloud\Spanner\SpannerClient;
use Testing\Data\User;
use Testing\Data\Book;

/**
 * Queries sample data from the database using proto columns.
 * Example:
 * ```
 * query_data_with_proto_columns($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function query_data_with_proto_columns(string $instanceId, string $databaseId): void
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    // this ensures that the User class has been loaded into the descriptor pool
    \GPBMetadata\Data\User::initOnce();

    $nameValue = 'TestUser 3';
    // [START spanner_query_data_with_proto_columns]
    $results = $database->execute(
        'SELECT * FROM Users '
        . 'WHERE User.name = @name',
        [
            'parameters' => [
                'name' => $nameValue
            ],
        ]
    );
    foreach ($results as $row) {
        /** @var User $user */
        $user = $row['User'];
        printf('User: %s' . PHP_EOL, $user->serializeToJsonString());
        /** @var Book $book */
        foreach ($row['Books'] ?? [] as $book) {
            printf('Book: %s' . PHP_EOL, $book->serializeToJsonString());
        }
    }
    // [END spanner_query_data_with_proto_columns]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
