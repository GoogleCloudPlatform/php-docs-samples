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

// [START spanner_cancel_backup_create]
use Google\Cloud\Spanner\SpannerClient;

/**
 * Cancel a backup operation.
 * Example:
 * ```
 * cancel_backup_operation($instanceId, $databaseId);
 * ```
 *
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 */
function cancel_backup_operation($instanceId, $databaseId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $database = $instance->database($databaseId);
    $backupId = uniqid('backup-' . $databaseId . '-cancel');

    $expireTime = new \DateTime('+14 days');
    $backup = $instance->backup($backupId);
    $operation = $backup->create($database->name(), $expireTime);
    $operation->cancel();
    print('Waiting for operation to complete ...' . PHP_EOL);
    $operation->pollUntilComplete();

    // Cancel operations are always successful regardless of whether the operation is
    // still in progress or is complete.
    printf('Cancel backup operation complete.' . PHP_EOL);

    // Operation may succeed before cancel() has been called. So we need to clean up created backup.
    if ($backup->exists()) {
        $backup->delete();
    }
}
// [END spanner_cancel_backup_create]
