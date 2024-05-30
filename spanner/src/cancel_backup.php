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

// [START spanner_cancel_backup_create]
use Google\ApiCore\ApiException;
use Google\Cloud\Spanner\Admin\Database\V1\Backup;
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupRequest;
use Google\Cloud\Spanner\Admin\Database\V1\DeleteBackupRequest;
use Google\Cloud\Spanner\Admin\Database\V1\GetBackupRequest;
use Google\Protobuf\Timestamp;

/**
 * Cancel a backup operation.
 * Example:
 * ```
 * cancel_backup($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function cancel_backup(string $projectId, string $instanceId, string $databaseId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseFullName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    $instanceFullName = DatabaseAdminClient::instanceName($projectId, $instanceId);
    $expireTime = new Timestamp();
    $expireTime->setSeconds((new \DateTime('+14 days'))->getTimestamp());
    $backupId = uniqid('backup-' . $databaseId . '-cancel');
    $request = new CreateBackupRequest([
        'parent' => $instanceFullName,
        'backup_id' => $backupId,
        'backup' => new Backup([
            'database' => $databaseFullName,
            'expire_time' => $expireTime
        ])
    ]);

    $operation = $databaseAdminClient->createBackup($request);
    $operation->cancel();

    // Cancel operations are always successful regardless of whether the operation is
    // still in progress or is complete.
    printf('Cancel backup operation complete.' . PHP_EOL);

    // Operation may succeed before cancel() has been called. So we need to clean up created backup.
    try {
        $request = new GetBackupRequest();
        $request->setName($databaseAdminClient->backupName($projectId, $instanceId, $backupId));
        $info = $databaseAdminClient->getBackup($request);
    } catch (ApiException $ex) {
        return;
    }
    $databaseAdminClient->deleteBackup(new DeleteBackupRequest([
        'name' => $databaseAdminClient->backupName($projectId, $instanceId, $backupId)
    ]));
}
// [END spanner_cancel_backup_create]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
