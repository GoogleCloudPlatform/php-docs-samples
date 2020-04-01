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

    // Expire time must be within 366 days of the create time of the backup.
    $newTimestamp = new \DateTime('+30 days');
    $backup->updateExpireTime($newTimestamp);

    print("Backup $backupId new expire time: " . $backup->info()['expireTime'] . PHP_EOL);
}
// [END spanner_update_backup]
