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

// [START spanner_update_backup]
use Google\Cloud\Spanner\Admin\Database\V1\Backup;
use Google\Cloud\Spanner\Admin\Database\V1\GetBackupRequest;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateBackupRequest;
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Protobuf\Timestamp;

/**
 * Update the backup expire time.
 * Example:
 * ```
 * update_backup($projectId, $instanceId, $backupId);
 * ```
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $backupId The Spanner backup ID.
 */
function update_backup(string $projectId, string $instanceId, string $backupId): void
{
    $databaseAdminClient = new DatabaseAdminClient();
    $backupName = DatabaseAdminClient::backupName($projectId, $instanceId, $backupId);
    $newExpireTime = new Timestamp();
    $newExpireTime->setSeconds((new \DateTime('+30 days'))->getTimestamp());
    $request = new UpdateBackupRequest([
        'backup' => new Backup([
            'name' => $backupName,
            'expire_time' => $newExpireTime
        ]),
        'update_mask' => new \Google\Protobuf\FieldMask(['paths' => ['expire_time']])
    ]);

    $info = $databaseAdminClient->updateBackup($request);
    printf('Backup %s new expire time: %d' . PHP_EOL, basename($info->getName()), $info->getExpireTime()->getSeconds());
}
// [END spanner_update_backup]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
