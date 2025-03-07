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
use Google\Cloud\Spanner\Proto;
use Testing\Data\User;
use Testing\Data\Book;

/**
 * Queries sample data from the database using proto columns.
 * Example:
 * ```
 * query_data_with_proto_columns($instanceId, $databaseId, $userId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param int $userId The ID of the user to query.
 */
function query_data_with_proto_columns(
    string $instanceId,
    string $databaseId,
    int $userId = 1
): void {
    // [START spanner_query_data_with_proto_columns]
    $spanner = new SpannerClient();
    $database = $spanner->instance($instanceId)->database($databaseId);

    $userProto = (new User())
        ->setName('Test User ' . $userId);

    $results = $database->execute(
        'SELECT * FROM Users, UNNEST(Books) as Book '
        . 'WHERE User.name = @user.name '
        . 'AND Book.title = @bookTitle',
        [
            'parameters' => [
                'user' => $userProto,
                'bookTitle' => 'Book 1',
            ],
        ]
    );
    foreach ($results as $row) {
        /** @var User $user */
        $user = $row['User']->get();
        // Print the decoded Protobuf message as JSON
        printf('User: %s' . PHP_EOL, $user->serializeToJsonString());
        /** @var Proto<Book> $book */
        foreach ($row['Books'] ?? [] as $book) {
            // Print the raw row value
            printf('Book: %s (%s)' . PHP_EOL, $book->getValue(), $book->getProtoTypeFqn());
        }
    }
    // [END spanner_query_data_with_proto_columns]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
