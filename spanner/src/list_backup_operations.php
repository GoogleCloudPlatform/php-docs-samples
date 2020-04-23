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
 * Example:
 * ```
 * list_backup_operations($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function list_backup_operations($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);

    // List the CreateBackup operations.
    $filter = "(metadata.database:$databaseId) AND " .
              "(metadata.@type:type.googleapis.com/" .
              "google.spanner.admin.database.v1.CreateBackupMetadata)";

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
}
// [END spanner_list_backup_operations]
