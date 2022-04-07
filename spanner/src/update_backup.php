<?php
/**
 * Copyright 2019 Google Inc.
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

// [START spanner_update_backup]
use Google\Cloud\Spanner\SpannerClient;
use DateTime;

/**
 * Update the backup expire time.
 * Example:
 * ```
 * update_backup($instanceId, $backupId);
 * ```
 * @param string $instanceId The Spanner instance ID.
 * @param string $backupId The Spanner backup ID.
 */
function update_backup($instanceId, $backupId)
{
    $spanner = new SpannerClient();
    $instance = $spanner->instance($instanceId);
    $backup = $instance->backup($backupId);
    $backup->reload();

    $newExpireTime = new DateTime('+30 days');
    $maxExpireTime = new DateTime($backup->info()['maxExpireTime']);
    // The new expire time can't be greater than maxExpireTime for the backup.
    $newExpireTime = min($newExpireTime, $maxExpireTime);

    $backup->updateExpireTime($newExpireTime);

    printf('Backup %s new expire time: %s' . PHP_EOL, $backupId, $backup->info()['expireTime']);
}
// [END spanner_update_backup]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
