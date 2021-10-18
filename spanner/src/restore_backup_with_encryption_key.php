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

// [START spanner_restore_backup_with_encryption_key]
use Google\Cloud\Spanner\Admin\Database\V1\RestoreDatabaseEncryptionConfig;
use Google\Cloud\Spanner\SpannerClient;

/**
 * Restore a database from a backup.
 * Example:
 * ```
 * restore_backup_with_encryption_key($instanceId, $databaseId, $backupId, $kmsKeyName);
 * ```
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID.
 * @param string $kmsKeyName The KMS key used for encryption.
 */
function restore_backup_with_encryption_key($instanceId, $databaseId, $backupId, $kmsKeyName)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $backup = $instance->backup($backupId);

    $operation = $database->restore($backup->name(), [
        'encryptionConfig' => [
            'kmsKeyName' => $kmsKeyName,
            'encryptionType' => RestoreDatabaseEncryptionConfig\EncryptionType::CUSTOMER_MANAGED_ENCRYPTION
        ]
    ]);
    // Wait for restore operation to complete.
    $operation->pollUntilComplete();

    // Newly created database has restore information.
    $database->reload();
    $restoreInfo = $database->info()['restoreInfo'];
    $sourceDatabase = $restoreInfo['backupInfo']['sourceDatabase'];
    $sourceBackup = $restoreInfo['backupInfo']['backup'];
    $encryptionConfig = $database->info()['encryptionConfig'];

    printf(
        'Database %s restored from backup %s using encryption key %s' . PHP_EOL,
        $sourceDatabase, $sourceBackup, $encryptionConfig['kmsKeyName']);
}
// [END spanner_restore_backup_with_encryption_key]

require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
