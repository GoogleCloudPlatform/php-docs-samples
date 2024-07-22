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

// [START spanner_create_backup]
use Google\Cloud\Spanner\Admin\Database\V1\Backup;
use Google\Cloud\Spanner\Admin\Database\V1\GetBackupRequest;
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupRequest;
use Google\Protobuf\Timestamp;

/**
 * Create a backup.
 * Example:
 * ```
 * create_backup($projectId, $instanceId, $databaseId, $backupId, $versionTime);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID.
 * @param string $versionTime The version of the database to backup. Read more
 * at https://cloud.google.com/spanner/docs/reference/rest/v1/projects.instances.backups#Backup.FIELDS.version_time
 */
function create_backup(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $backupId,
    string $versionTime = '-1hour'
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseFullName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    $instanceFullName = DatabaseAdminClient::instanceName($projectId, $instanceId);
    $timestamp = new Timestamp();
    $timestamp->setSeconds((new \DateTime($versionTime))->getTimestamp());
    $expireTime = new Timestamp();
    $expireTime->setSeconds((new \DateTime('+14 days'))->getTimestamp());
    $request = new CreateBackupRequest([
        'parent' => $instanceFullName,
        'backup_id' => $backupId,
        'backup' => new Backup([
            'database' => $databaseFullName,
            'expire_time' => $expireTime,
            'version_time' => $timestamp
        ])
    ]);

    $operation = $databaseAdminClient->createBackup($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $request = new GetBackupRequest();
    $request->setName($databaseAdminClient->backupName($projectId, $instanceId, $backupId));
    $info = $databaseAdminClient->getBackup($request);
    printf(
        'Backup %s of size %d bytes was created at %d for version of database at %d' . PHP_EOL,
        basename($info->getName()),
        $info->getSizeBytes(),
        $info->getCreateTime()->getSeconds(),
        $info->getVersionTime()->getSeconds()
    );
}
// [END spanner_create_backup]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
