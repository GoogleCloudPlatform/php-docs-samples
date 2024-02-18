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

// [START spanner_restore_backup_with_encryption_key]
use Google\Cloud\Spanner\Admin\Database\V1\Backup;
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\EncryptionConfig;
use Google\Cloud\Spanner\Admin\Database\V1\RestoreDatabaseEncryptionConfig;
use Google\Cloud\Spanner\Admin\Database\V1\RestoreDatabaseMetadata;
use Google\Cloud\Spanner\Admin\Database\V1\RestoreDatabaseRequest;
use Google\Cloud\Spanner\Database;
use Google\Protobuf\FieldMask;
use Google\Protobuf\Timestamp;

/**
 * Restore a database from a backup.
 * Example:
 * ```
 * restore_backup_with_encryption_key($projectId, $instanceId, $databaseId, $backupId, $kmsKeyName);
 * ```
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID.
 * @param string $kmsKeyName The KMS key used for encryption.
 */
function restore_backup_with_encryption_key(string $projectId, string $instanceId, string $databaseId, string $backupId, string $kmsKeyName): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $instanceFullName = DatabaseAdminClient::instanceName($projectId, $instanceId);
    $backupFullName = DatabaseAdminClient::backupName($projectId, $instanceId, $backupId);
    $request = new RestoreDatabaseRequest([
        'parent' => $instanceFullName,
        'database_id' => $databaseId,
        'backup' => $backupFullName,
        'encryption_config' => new RestoreDatabaseEncryptionConfig([
            'kms_key_name' => $kmsKeyName,
            'encryption_type' => RestoreDatabaseEncryptionConfig\EncryptionType::CUSTOMER_MANAGED_ENCRYPTION
        ])
    ]);

    // Create restore operation
    $operation = $databaseAdminClient->restoreDatabase($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    // Reload new database and get restore info
    $database = $operation->operationSucceeded() ? $operation->getResult() : null;
    $restoreInfo = $database->getRestoreInfo();
    $backupInfo = $restoreInfo->getBackupInfo();
    $sourceDatabase = $backupInfo->getSourceDatabase();
    $sourceBackup = $backupInfo->getBackup();
    $encryptionConfig = $database->getEncryptionConfig();
    printf(
        'Database %s restored from backup %s using encryption key %s' . PHP_EOL,
        $sourceDatabase, $sourceBackup, $encryptionConfig->getKmsKeyName()
    );
}
// [END spanner_restore_backup_with_encryption_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
