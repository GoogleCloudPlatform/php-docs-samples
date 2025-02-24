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

// [START spanner_create_database_with_proto_columns]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateDatabaseRequest;

/**
 * Creates a database and tables for sample data using proto columns.
 * Example:
 * ```
 * create_database_with_proto_columns($instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function create_database_with_proto_columns(
    string $projectId,
    string $instanceId,
    string $databaseId
): void {
    // The result of running `protoc` with `--descriptor_set_out` on your proto file(s)
    $fileDescriptorSet = file_get_contents('data/user.pb');

    $databaseAdminClient = new DatabaseAdminClient();
    $instance = $databaseAdminClient->instanceName($projectId, $instanceId);

    $operation = $databaseAdminClient->createDatabase(
        new CreateDatabaseRequest([
            'parent' => $instance,
            'create_statement' => sprintf('CREATE DATABASE `%s`', $databaseId),
            'proto_descriptors' => $fileDescriptorSet,
            'extra_statements' => [
                'CREATE PROTO BUNDLE (' .
                    'testing.data.User,' .
                    'testing.data.User.Address,' .
                    'testing.data.Book' .
                ')',
                'CREATE TABLE Users (' .
                    'Id INT64,' .
                    'User `testing.data.User`,' .
                    'Books ARRAY<`testing.data.Book`>,' .
                ') PRIMARY KEY (Id)'
            ],
        ])
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created database %s on instance %s' . PHP_EOL, $databaseId, $instanceId);
}
// [END spanner_create_database_with_proto_columns]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
