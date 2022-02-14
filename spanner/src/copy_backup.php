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
 * Create a copy backup from another source backup.
 * Example:
 * ```
 * copy_backup($destInstanceId, $destBackupId, $sourceInstanceId, $sourceBackupId);
 * ```
 *
 * @param string $destInstanceId The Spanner instance ID where the copy backup will reside.
 * @param string $destBackupId The Spanner backup ID of the new backup to be created.
 * @param string $sourceInstanceId The Spanner instance ID of the source backup.
 * @param string $sourceBackupId The Spanner backup ID of the source.
 */
function copy_backup($destInstanceId, $destBackupId, $sourceInstanceId, $sourceBackupId)
{
    $spanner = new SpannerClient();

    $destInstance = $spanner->instance($destInstanceId);
    $sourceInstance = $spanner->instance($sourceInstanceId);
    $sourceBackup = $sourceInstance->backup($sourceBackupId);
    $destBackup = $destInstance->backup($destBackupId);

    $expireTime = new \DateTime('+8 hours');
    $operation = $sourceBackup->createCopy($destBackup, $expireTime);

    print('Waiting for operation to complete...' . PHP_EOL);

    $operation->pollUntilComplete();
    $destBackup->reload();

    $ready = ($destBackup->state() == Backup::STATE_READY);

    if ($ready) {
        print('Backup is ready!' . PHP_EOL);
        $info = $destBackup->info();
        printf(
            'Backup %s of size %d bytes was copied at %s from the source backup %s' . PHP_EOL,
            basename($info['name']), $info['sizeBytes'], $info['createTime'], $sourceBackupId);
        printf('Version time of the copied backup: %s' . PHP_EOL, $info['versionTime']);
    } else {
        printf('Unexpected state: %s' . PHP_EOL, $destBackup->state());
    }
}
// [END spanner_copy_backup]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
