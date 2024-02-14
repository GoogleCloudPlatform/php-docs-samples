<?php
/**
 * Copyright 2024 Google Inc.
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

// [START spanner_create_database_with_encryption_key]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateDatabaseRequest;
use Google\Cloud\Spanner\Admin\Database\V1\EncryptionConfig;

/**
 * Creates an encrypted database with tables for sample data.
 * Example:
 * ```
 * create_database_with_encryption_key($projectId, $instanceId, $databaseId, $kmsKeyName);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $kmsKeyName The KMS key used for encryption.
 */
function create_database_with_encryption_key(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $kmsKeyName = ''
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $instanceName = DatabaseAdminClient::instanceName($projectId, $instanceId);

    $createDatabaseRequest = new CreateDatabaseRequest();
    $createDatabaseRequest->setParent($instanceName);
    $createDatabaseRequest->setCreateStatement(sprintf('CREATE DATABASE `%s`', $databaseId));
    $createDatabaseRequest->setExtraStatements([
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
    ]);

    if (!empty($kmsKeyName)) {
        $encryptionConfig = new EncryptionConfig();
        $encryptionConfig->setKmsKeyName($kmsKeyName);
        $createDatabaseRequest->setEncryptionConfig($encryptionConfig);
    }

    $operationResponse = $databaseAdminClient->createDatabase($createDatabaseRequest);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $database = $operationResponse->getResult();
        printf(
            'Created database %s on instance %s with encryption key %s' . PHP_EOL,
            $databaseId,
            $instanceId,
            $database->getEncryptionConfig()->getKmsKeyName()
        );
    } else {
        $error = $operationResponse->getError();
        printf('Failed to create encrypted database: %s' . PHP_EOL, $error->getMessage());
    }
}
// [END spanner_create_database_with_encryption_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
