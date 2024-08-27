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

// [START spanner_create_backup_with_MR_CMEK]
use Google\Cloud\Spanner\Admin\Database\V1\Backup;
use \Google\Cloud\Spanner\Admin\Database\V1\Backup\State;
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupEncryptionConfig;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupRequest;
use Google\Cloud\Spanner\Admin\Database\V1\GetBackupRequest;
use Google\Protobuf\Timestamp;

/**
 * Create a CMEK backup.
 * Example:
 * ```
 * create_backup_with_MR_CMEK($projectId, $instanceId, $databaseId, $backupId, $kmsKeyNames);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID.
 * @param string[] $kmsKeyNames The KMS keys used for encryption.
 */
function create_backup_with_MR_CMEK(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $backupId,
    array $kmsKeyNames
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $instanceFullName = DatabaseAdminClient::instanceName($projectId, $instanceId);
    $databaseFullName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    $expireTime = new Timestamp();
    $expireTime->setSeconds((new \DateTime('+14 days'))->getTimestamp());
    $request = new CreateBackupRequest([
        'parent' => $instanceFullName,
        'backup_id' => $backupId,
        'encryption_config' => new CreateBackupEncryptionConfig([
            'kms_key_names' => $kmsKeyNames,
            'encryption_type' => CreateBackupEncryptionConfig\EncryptionType::CUSTOMER_MANAGED_ENCRYPTION
        ]),
        'backup' => new Backup([
            'database' => $databaseFullName,
            'expire_time' => $expireTime
        ])
    ]);

    $operation = $databaseAdminClient->createBackup($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $request = new GetBackupRequest();
    $request->setName($databaseAdminClient->backupName($projectId, $instanceId, $backupId));
    $info = $databaseAdminClient->getBackup($request);
    if (State::name($info->getState()) == 'READY') {
        printf(
            'Backup %s of size %d bytes was created at %d using encryption keys %s' . PHP_EOL,
            basename($info->getName()),
            $info->getSizeBytes(),
            $info->getCreateTime()->getSeconds(),
            print_r($info->getEncryptionInfo()->getKmsKeyVersions(), true)
        );
    } else {
        print('Backup is not ready!' . PHP_EOL);
    }
}
// [END spanner_create_backup_with_MR_CMEK]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
