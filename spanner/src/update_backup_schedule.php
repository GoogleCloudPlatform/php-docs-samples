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

// [START spanner_update_backup_schedule]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\UpdateBackupScheduleRequest;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupEncryptionConfig;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupEncryptionConfig\EncryptionType;
use Google\Cloud\Spanner\Admin\Database\V1\BackupSchedule;
use Google\Cloud\Spanner\Admin\Database\V1\FullBackupSpec;
use Google\Cloud\Spanner\Admin\Database\V1\BackupScheduleSpec;
use Google\Cloud\Spanner\Admin\Database\V1\CrontabSpec;
use Google\Protobuf\Duration;
use Google\Protobuf\FieldMask;

/**
 * Update an existing backup schedule.
 * Example:
 * ```
 * update_backup_schedule($projectId, $instanceId, $databaseId, $backupScheduleId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupScheduleId The ID of the backup schedule to be created.
 * at https://cloud.google.com/spanner/docs/reference/rest/v1/projects.instances.databases.backupSchedules#BackupSchedule.FIELDS
 */
function update_backup_schedule(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $backupScheduleId,
): void {
    $databaseAdminClient = new DatabaseAdminClient();

    $encryptionConfig = new CreateBackupEncryptionConfig([
        'encryption_type' => EncryptionType::USE_DATABASE_ENCRYPTION,
    ]);
    $backupScheduleName = sprintf(
        'projects/%s/instances/%s/databases/%s/backupSchedules/%s', 
        $projectId,
        $instanceId,
        $databaseId,
        $backupScheduleId);
    $backupSchedule = new BackupSchedule([
        'name' => $backupScheduleName,
        'full_backup_spec' => new FullBackupSpec(),
        'retention_duration' => (new Duration())
            ->setSeconds(48 * 60 * 60),
        'spec' => new BackupScheduleSpec([
            'cron_spec' => new CrontabSpec([
                'text' => '45 15 * * *'
            ]),
        ]),
        'encryption_config' => $encryptionConfig,
    ]);
    $fieldMask = (new FieldMask())
        ->setPaths([
            'retention_duration',
            'spec.cron_spec.text',
            'encryption_config',
    ]);

    $request = new UpdateBackupScheduleRequest([
        'backup_schedule' => $backupSchedule,
        'update_mask' => $fieldMask,
    ]);

    $operation = $databaseAdminClient->updateBackupSchedule($request);

    printf('Updated backup scehedule %s'  . PHP_EOL, $operation->getName());
}
// [END spanner_update_backup_schedule]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);

