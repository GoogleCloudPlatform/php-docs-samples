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

// [START spanner_create_backup_schedule]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupScheduleRequest;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupEncryptionConfig;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupEncryptionConfig\EncryptionType;
use Google\Cloud\Spanner\Admin\Database\V1\BackupSchedule;
use Google\Cloud\Spanner\Admin\Database\V1\FullBackupSpec;
use Google\Cloud\Spanner\Admin\Database\V1\BackupScheduleSpec;
use Google\Cloud\Spanner\Admin\Database\V1\CrontabSpec;
use Google\Protobuf\Duration;

/**
 * Create a backup schedule.
 * Example:
 * ```
 * create_backup_schedule($projectId, $instanceId, $databaseId, $backupScheduleId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupScheduleId The ID of the backup schedule to be created.
 * at https://cloud.google.com/spanner/docs/reference/rest/v1/projects.instances.databases.backupSchedules#BackupSchedule.FIELDS
 */
function create_backup_schedule(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $backupScheduleId,
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseFullName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);
    printf('%s', $databaseFullName);

    $encryptionConfig = (new CreateBackupEncryptionConfig())
        ->setEncryptionType(EncryptionType::USE_DATABASE_ENCRYPTION);
    $backupSchedule = new BackupSchedule([
        'full_backup_spec' => new FullBackupSpec(),
        'retention_duration' => (new Duration())
            ->setSeconds(24 * 60 * 60),
        'spec' => new BackupScheduleSpec([
            'cron_spec' => new CrontabSpec([
                'text' => '30 12 * * *'
            ]),
        ]),
        'encryption_config' => $encryptionConfig,
    ]);
    $request = new CreateBackupScheduleRequest([
        'parent' => $databaseFullName,
        'backup_schedule_id' => $backupScheduleId,
        'backup_schedule' => $backupSchedule,
    ]);

    $operation = $databaseAdminClient->createBackupSchedule($request);

    printf('Created backup scehedule %s' . PHP_EOL, $operation->getName());
}
// [END spanner_create_backup_schedule]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);