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

// [START spanner_list_backup_schedules]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\ListBackupSchedulesRequest;

/**
 * Get list of all backup schedules for a given database.
 * Example:
 * ```
 * list_backup_schedules($projectId, $instanceId, $databaseId, $backupScheduleId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * at https://cloud.google.com/spanner/docs/reference/rest/v1/projects.instances.databases.backupSchedules#BackupSchedule.FIELDS
 */
function list_backup_schedules(
    string $projectId,
    string $instanceId,
    string $databaseId,
): void {
    $databaseAdminClient = new DatabaseAdminClient();
    $databaseFullName = DatabaseAdminClient::databaseName($projectId, $instanceId, $databaseId);

    $request = new ListBackupSchedulesRequest([
        'parent' => $databaseFullName,
    ]);
    $operation = $databaseAdminClient->listBackupSchedules($request);

    printf("Backup schedules for database %s" . PHP_EOL, $databaseFullName);
    foreach ($operation as $schedule) {
        printf("Backup schedule: %s" . PHP_EOL, $schedule->getName());
    }
}
// [END spanner_list_backup_schedules]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);