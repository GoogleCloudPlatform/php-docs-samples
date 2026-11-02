<?php

/**
 * Copyright 2026 Google LLC
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storagecontrol/README.md
 */

namespace Google\Cloud\Samples\StorageControl;

# [START storage_control_disable_rapid_cache]
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\DisableRapidCacheRequest;
use Google\Cloud\Storage\Control\V2\RapidCache;

/**
 * Disables a Rapid Cache instance.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $rapidCacheId The ID of the Rapid Cache.
 *        (e.g. 'us-central1-a')
 * @param int $timeoutMillis Optional. The maximum time to wait for the
 *        operation to complete, in milliseconds. 0 waits until it completes.
 *        (e.g. 600000 for 10 minutes)
 */
function disable_rapid_cache(string $bucketName, string $rapidCacheId, int $timeoutMillis = 0): void
{
    $storageControlClient = new StorageControlClient();

    // Set project to "_" to signify global bucket
    $formattedName = $storageControlClient->rapidCacheName('_', $bucketName, $rapidCacheId);

    $request = new DisableRapidCacheRequest([
        'name' => $formattedName,
    ]);

    // Disabling a Rapid Cache is a long-running operation. This sample waits
    // for the long-running operation to complete. A totalPollTimeoutMillis of
    // 0 waits with no time limit.
    $operation = $storageControlClient->disableRapidCache($request);
    $completed = $operation->pollUntilComplete([
        'totalPollTimeoutMillis' => $timeoutMillis,
    ]);

    if (!$completed) {
        printf('Disable operation is still running: %s' . PHP_EOL, $operation->getName());
        return;
    }

    if (!$operation->operationSucceeded()) {
        printf('Error: %s' . PHP_EOL, $operation->getError()->getMessage());
        return;
    }

    /** @var RapidCache $rapidCacheResult */
    $rapidCacheResult = $operation->getResult();
    printf('Disabled Rapid Cache: %s' . PHP_EOL, $rapidCacheResult->getName());
}
# [END storage_control_disable_rapid_cache]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
