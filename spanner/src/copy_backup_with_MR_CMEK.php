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

// [START spanner_copy_backup_with_MR_CMEK]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CopyBackupRequest;
use Google\Cloud\Spanner\Admin\Database\V1\CopyBackupEncryptionConfig;
use Google\Protobuf\Timestamp;

/**
 * Copy a MR CMEK backup.
 * Example:
 * ```
 * copy_backup_with_MR_CMEK($projectId, $instanceId, $sourceBackupId, $backupId, $kmsKeyNames);
 * ```
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $sourceBackupId The Spanner source backup ID.
 * @param string $backupId The Spanner backup ID.
 * @param string[] $kmsKeyNames The KMS keys used for encryption.
 */
/**
 * Create a copy MR CMEK backup from another source backup.
 * Example:
 * ```
 * copy_backup_with_MR_CMEK($projectId, $destInstanceId, $destBackupId, $sourceInstanceId, $sourceBackupId, $kmsKeyNames);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $destInstanceId The Spanner instance ID where the copy backup will reside.
 * @param string $destBackupId The Spanner backup ID of the new backup to be created.
 * @param string $sourceInstanceId The Spanner instance ID of the source backup.
 * @param string $sourceBackupId The Spanner backup ID of the source.
* @param string[] $kmsKeyNames The KMS keys used for encryption.
 */
function copy_backup_with_MR_CMEK(
    string $projectId,
    string $destInstanceId,
    string $destBackupId,
    string $sourceInstanceId,
    string $sourceBackupId,
    array $kmsKeyNames
): void {
    $databaseAdminClient = new DatabaseAdminClient();

    $destInstanceFullName = DatabaseAdminClient::instanceName($projectId, $destInstanceId);
    $expireTime = new Timestamp();
    $expireTime->setSeconds((new \DateTime('+8 hours'))->getTimestamp());
    $sourceBackupFullName = DatabaseAdminClient::backupName($projectId, $sourceInstanceId, $sourceBackupId);
    $request = new CopyBackupRequest([
        'source_backup' => $sourceBackupFullName,
        'parent' => $destInstanceFullName,
        'backup_id' => $destBackupId,
        'expire_time' => $expireTime,
        'encryption_config' => new CopyBackupEncryptionConfig([
            'kms_key_names' => $kmsKeyNames,
            'encryption_type' => CopyBackupEncryptionConfig\EncryptionType::CUSTOMER_MANAGED_ENCRYPTION
        ])
    ]);

    $operationResponse = $databaseAdminClient->copyBackup($request);
    $operationResponse->pollUntilComplete();

    if (!$operationResponse->operationSucceeded()) {
        $error = $operationResponse->getError();
        printf('Backup not created due to error: %s.' . PHP_EOL, $error->getMessage());
        return;
    }
    $destBackupInfo = $operationResponse->getResult();
    $kmsKeyVersions = [];
    foreach ($destBackupInfo->getEncryptionInformation() as $encryptionInfo) {
        $kmsKeyVersions[] = $encryptionInfo->getKmsKeyVersion();
    }
    printf(
        'Backup %s of size %d bytes was copied at %d from the source backup %s using encryption keys %s' . PHP_EOL,
        basename($destBackupInfo->getName()),
        $destBackupInfo->getSizeBytes(),
        $destBackupInfo->getCreateTime()->getSeconds(),
        $sourceBackupId,
        print_r($kmsKeyVersions, true)
    );
    printf('Version time of the copied backup: %d' . PHP_EOL, $destBackupInfo->getVersionTime()->getSeconds());
}
// [END spanner_copy_backup_with_MR_CMEK]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
