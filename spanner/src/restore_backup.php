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

// [START spanner_restore_backup]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\RestoreDatabaseRequest;

/**
 * Restore a database from a backup.
 * Example:
 * ```
 * restore_backup($projectId, $instanceId, $databaseId, $backupId);
 * ```
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID.
 */
function restore_backup(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $backupId
): void {
    $databaseAdminClient = new DatabaseAdminClient();

    $backupName = DatabaseAdminClient::backupName($projectId, $instanceId, $backupId);
    $instanceName = DatabaseAdminClient::instanceName($projectId, $instanceId);

    $request = new RestoreDatabaseRequest([
        'parent' => $instanceName,
        'database_id' => $databaseId,
        'backup' => $backupName
    ]);

    $operationResponse = $databaseAdminClient->restoreDatabase($request);
    $operationResponse->pollUntilComplete();

    $database = $operationResponse->operationSucceeded() ? $operationResponse->getResult() : null;
    $restoreInfo = $database->getRestoreInfo();
    $backupInfo = $restoreInfo->getBackupInfo();
    $sourceDatabase = $backupInfo->getSourceDatabase();
    $sourceBackup = $backupInfo->getBackup();
    $versionTime = $backupInfo->getVersionTime()->getSeconds();
    printf(
        'Database %s restored from backup %s with version time %s' . PHP_EOL,
        $sourceDatabase, $sourceBackup, $versionTime
    );
}
// [END spanner_restore_backup]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
