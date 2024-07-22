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

// [START spanner_list_backup_operations]
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;
use Google\Cloud\Spanner\Admin\Database\V1\CreateBackupMetadata;
use Google\Cloud\Spanner\Admin\Database\V1\CopyBackupMetadata;
use Google\Cloud\Spanner\Admin\Database\V1\ListBackupOperationsRequest;

/**
 * List all create backup operations in an instance.
 * Optionally passing the backupId will also list the
 * copy backup operations on the backup.
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID whose copy operations need to be listed.
 */
function list_backup_operations(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $backupId
): void {
    $databaseAdminClient = new DatabaseAdminClient();

    $parent = DatabaseAdminClient::instanceName($projectId, $instanceId);

    // List the CreateBackup operations.
    $filterCreateBackup = '(metadata.@type:type.googleapis.com/' .
        'google.spanner.admin.database.v1.CreateBackupMetadata) AND ' . "(metadata.database:$databaseId)";

    // See https://cloud.google.com/spanner/docs/reference/rpc/google.spanner.admin.database.v1#listbackupoperationsrequest
    // for the possible filter values
    $filterCopyBackup = sprintf('(metadata.@type:type.googleapis.com/' .
        'google.spanner.admin.database.v1.CopyBackupMetadata) AND ' . "(metadata.source_backup:$backupId)");
    $operations = $databaseAdminClient->listBackupOperations(
        new ListBackupOperationsRequest([
            'parent' => $parent,
            'filter' => $filterCreateBackup
        ])
    );

    foreach ($operations->iterateAllElements() as $operation) {
        $obj = new CreateBackupMetadata();
        $meta = $operation->getMetadata()->unpack($obj);
        $backupName = basename($meta->getName());
        $dbName = basename($meta->getDatabase());
        $progress = $meta->getProgress()->getProgressPercent();
        printf('Backup %s on database %s is %d%% complete.' . PHP_EOL, $backupName, $dbName, $progress);
    }

    $operations = $databaseAdminClient->listBackupOperations(
        new ListBackupOperationsRequest([
            'parent' => $parent,
            'filter' => $filterCopyBackup
        ])
    );

    foreach ($operations->iterateAllElements() as $operation) {
        $obj = new CopyBackupMetadata();
        $meta = $operation->getMetadata()->unpack($obj);
        $backupName = basename($meta->getName());
        $progress = $meta->getProgress()->getProgressPercent();
        printf('Copy Backup %s on source backup %s is %d%% complete.' . PHP_EOL, $backupName, $backupId, $progress);
    }
}
// [END spanner_list_backup_operations]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
