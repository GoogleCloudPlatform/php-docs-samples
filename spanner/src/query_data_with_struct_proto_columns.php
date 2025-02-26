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
use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\StructType;
use Testing\Data\Book;

/**
 * Queries sample data from the database using proto columns.
 * Example:
 * ```
 * query_data_with_struct_proto_columns($instanceId, $databaseId, $userId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function query_data_with_struct_proto_columns(
    string $instanceId,
    string $databaseId,
): void {
    // [START spanner_query_data_with_struct_proto_columns]
    $spanner = new SpannerClient();
    $database = $spanner->instance($instanceId)->database($databaseId);

    $structType = (new StructType)
        ->add('title', Database::TYPE_STRING)
        ->add('author', Database::TYPE_PROTO);

    $results = $database->execute(
        'SELECT u.Id, u.User.name, ' .
            'ARRAY(SELECT AS STRUCT b.title, b.author ' .
            'FROM u.Books AS b) as book_struct '.
        'FROM Users AS u'
    );
    foreach ($results as $row) {
        // Print the decoded Protobuf message as JSON
        printf('User name: %s' . PHP_EOL, $row['name']);
        foreach ($row['book_struct'] as $bookStruct) {
            if (!class_exists(Book::class, false)) {
                // If you receive an error such as
                // "Unable to decode proto value. Descriptor not found for testing.data.User"
                // you may need to initialize the generated classes. This also happens when creating
                // an instance of the message class (e.g. `new User()`).
                \GPBMetadata\Data\User::initOnce();
            }
            printf('Book struct title: %s' . PHP_EOL, $bookStruct['title']);
            printf('Book struct author: %s' . PHP_EOL, $bookStruct['author']->get()->serializeToJsonString());
        }
    }
    // [END spanner_query_data_with_struct_proto_columns]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
