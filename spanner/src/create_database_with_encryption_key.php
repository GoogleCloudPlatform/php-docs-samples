<?php
/**
 * Copyright 2021 Google Inc.
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

// [START spanner_create_database_with_encryption_key]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Creates an encrypted database with tables for sample data.
 * Example:
 * ```
 * create_database_with_encryption_key($instanceId, $databaseId, $kmsKeyName);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $kmsKeyName The KMS key used for encryption.
 */
function create_database_with_encryption_key($instanceId, $databaseId, $kmsKeyName)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);

    if (!$instance->exists()) {
        throw new \LogicException("Instance $instanceId does not exist");
    }

    $operation = $instance->createDatabase($databaseId, [
        'statements' => [
            'CREATE TABLE Singers (
                SingerId     INT64 NOT NULL,
                FirstName    STRING(1024),
                LastName     STRING(1024),
                SingerInfo   BYTES(MAX)
            ) PRIMARY KEY (SingerId)',
            'CREATE TABLE Albums (
                SingerId     INT64 NOT NULL,
                AlbumId      INT64 NOT NULL,
                AlbumTitle   STRING(MAX)
            ) PRIMARY KEY (SingerId, AlbumId),
            INTERLEAVE IN PARENT Singers ON DELETE CASCADE'
        ],
        'encryptionConfig' => ['kmsKeyName' => $kmsKeyName]
    ]);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $database = $instance->database($databaseId);
    printf(
        'Created database %s on instance %s with encryption key %s' . PHP_EOL,
        $databaseId,
        $instanceId,
        $database->info()['encryptionConfig']['kmsKeyName']
    );
}
// [END spanner_create_database_with_encryption_key]

require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
