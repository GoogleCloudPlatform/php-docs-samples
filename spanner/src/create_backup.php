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

// [START spanner_create_backup]
use Google\Cloud\Spanner\Backup;
use Google\Cloud\Spanner\SpannerClient;

/**
 * Create a backup.
 * Example:
 * ```
 * create_backup($instanceId, $databaseId, $backupId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $backupId The Spanner backup ID.
 */
function create_backup($instanceId, $databaseId, $backupId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);

    $expireTime = new \DateTime('+14 days');
    $backup = $instance->backup($backupId);
    $operation = $backup->create($database->name(), $expireTime);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    $backup->reload();
    $ready = ($backup->state() == Backup::STATE_READY);

    if ($ready) {
        print('Backup is ready!' . PHP_EOL);
        $info = $backup->info();
        printf('Backup %s of size %d bytes was created at %s' . PHP_EOL, basename($info['name']), $info['sizeBytes'], $info['createTime']);
    } else {
        print('Backup is not ready!' . PHP_EOL);
    }
}
// [END spanner_create_backup]
