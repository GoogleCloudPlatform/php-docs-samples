<?php
/**
 * Copyright 2020 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_list_backup_operations]
use Google\Cloud\Spanner\SpannerClient;

/**
 * List all create backup operations in an instance.
 * Optionally passing the backupId will also list the
 * copy backup operations on the backup.
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID whose copy operations need to be listed.
 */
function list_backup_operations(
    string $instanceId,
    string $databaseId,
    string $backupId = null
): void {
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);

    // List the CreateBackup operations.
    $filter = '(metadata.@type:type.googleapis.com/' .
              'google.spanner.admin.database.v1.CreateBackupMetadata) AND ' . "(metadata.database:$databaseId)";

    // See https://cloud.google.com/spanner/docs/reference/rpc/google.spanner.admin.database.v1#listbackupoperationsrequest
    // for the possible filter values
    $operations = $instance->backupOperations(['filter' => $filter]);

    foreach ($operations as $operation) {
        if (!$operation->done()) {
            $meta = $operation->info()['metadata'];
            $backupName = basename($meta['name']);
            $dbName = basename($meta['database']);
            $progress = $meta['progress']['progressPercent'];
            printf('Backup %s on database %s is %d%% complete.' . PHP_EOL, $backupName, $dbName, $progress);
        }
    }

    if (is_null($backupId)) {
        return;
    }

    // List copy backup operations
    $filter = '(metadata.@type:type.googleapis.com/' .
              'google.spanner.admin.database.v1.CopyBackupMetadata) AND ' . "(metadata.source_backup:$backupId)";

    $operations = $instance->backupOperations(['filter' => $filter]);

    foreach ($operations as $operation) {
        if (!$operation->done()) {
            $meta = $operation->info()['metadata'];
            $backupName = basename($meta['name']);
            $progress = $meta['progress']['progressPercent'];
            printf('Copy Backup %s on source backup %s is %d%% complete.' . PHP_EOL, $backupName, $backupId, $progress);
        }
    }
}
// [END spanner_list_backup_operations]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
