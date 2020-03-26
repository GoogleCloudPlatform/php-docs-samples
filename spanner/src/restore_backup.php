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

// [START spanner_restore_backup]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Restore a database from a backup.
 * Example:
 * ```
 * restore_backup($instanceId, $databaseId, $backupId);
 * ```
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID.
 */
function restore_backup($instanceId, $databaseId, $backupId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $backup = $instance->backup($backupId);

    $operation = $database->restore($backup->name());
    // Wait for restore operation to complete.
    $operation->pollUntilComplete();

    // Newly created database has restore information.
    $database->reload();
    $restoreInfo = $database->info()['restoreInfo'];
    $sourceDatabase = $restoreInfo['backupInfo']['sourceDatabase'];
    $sourceBackup = $restoreInfo['backupInfo']['backup'];

    print("Database $sourceDatabase restored from backup $sourceBackup." . PHP_EOL);
}
// [END spanner_restore_backup]
