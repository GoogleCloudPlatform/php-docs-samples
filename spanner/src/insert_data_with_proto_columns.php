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

// [START spanner_insert_data_with_proto_columns]
use Google\Cloud\Spanner\SpannerClient;
use Testing\Data\User;
use Testing\Data\Book;

/**
 * Inserts sample data that can be used to test proto columns in queries.
 *
 * The database and table must already exist and can be created using
 * `create_database`.
 * Example:
 * ```
 * insert_data_with_proto_columns($instanceId, $databaseId, 1);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param int $userId The ID of the user to insert.
 */
function insert_data_with_proto_columns(
    string $instanceId,
    string $databaseId,
    int $userId,
): void {
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $user = new User();
    $user->setName('Test User ' . $userId);
    $address = new User\Address();
    $address->setCity('San Francisco');
    $address->setState('CA');
    $user->setAddress($address);

    $books = [
        new Book(['title' => 'Book 1', 'author' => 'Author 1']),
        new Book(['title' => 'Book 2', 'author' => 'Author 2']),
    ];

    $transaction = $database->transaction(['singleUse' => true])
        ->insertBatch('Users', [
            ['Id' => $userId, 'User' => $user, 'Books' => $books],
        ]);
    $transaction->commit();

    print('Inserted data.' . PHP_EOL);
}
// [END spanner_insert_data_with_proto_columns]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
