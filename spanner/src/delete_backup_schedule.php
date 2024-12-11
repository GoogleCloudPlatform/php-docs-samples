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

// [START spanner_delete_backup_schedule]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\DeleteBackupScheduleRequest;

/**
 * Delete a backup schedule.
 * Example:
 * ```
 * delete_backup_schedule($projectId, $instanceId, $databaseId, $backupScheduleId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupScheduleId The ID of the backup schedule to be created.
 * at https://cloud.google.com/spanner/docs/reference/rest/v1/projects.instances.databases.backupSchedules#BackupSchedule.FIELDS
 */
function delete_backup_schedule(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $backupScheduleId,
): void {
    $databaseAdminClient = new DatabaseAdminClient();

    $backupScheduleName = sprintf(
        'projects/%s/instances/%s/databases/%s/backupSchedules/%s',
        $projectId,
        $instanceId,
        $databaseId,
        $backupScheduleId
    );
    $request = new DeleteBackupScheduleRequest([
        'name' => strval($backupScheduleName),
    ]);

    $databaseAdminClient->deleteBackupSchedule($request);
    printf('Deleted backup scehedule %s' . PHP_EOL, $backupScheduleName);
}
// [END spanner_delete_backup_schedule]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
