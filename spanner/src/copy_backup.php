<?php
/**
 * Copyright 2021 Google Inc.
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

// [START spanner_copy_backup]
use Google\Cloud\Spanner\Backup;
use Google\Cloud\Spanner\SpannerClient;

/**
 * Copy a backup from another source backup.
 * Example:
 * ```
 * copy_backup($instanceId, $backupId, $sourceBackupId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $backupId The Spanner backup ID.
 * @param string $sourceBackupId The Spanner backup ID of the source.
 */
function copy_backup($instanceId, $backupId, $sourceBackupId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);

    $expireTime = new \DateTime('+8 hours');
    $backup = $instance->backup($sourceBackupId);
    $operation = $backup->createCopy($backupId, $expireTime);

    print('Waiting for operation to complete...' . PHP_EOL);

    $operation->pollUntilComplete();
    $newBackup = $instance->backup($backupId);
    $newBackup->reload();

    $ready = ($newBackup->state() == Backup::STATE_READY);

    if ($ready) {
        print('Backup is ready!' . PHP_EOL);
        $info = $newBackup->info();
        printf(
            'Backup %s of size %d bytes was copied at %s from the source backup %s' . PHP_EOL,
            basename($info['name']), $info['sizeBytes'], $info['createTime'], $sourceBackupId);
    } else {
        print('Backup is not ready!' . PHP_EOL);
    }
}
// [END spanner_copy_backup]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
