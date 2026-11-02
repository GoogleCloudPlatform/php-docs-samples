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

# [START storage_control_update_rapid_cache]
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\RapidCache;
use Google\Cloud\Storage\Control\V2\UpdateRapidCacheRequest;
use Google\Protobuf\Duration;
use Google\Protobuf\FieldMask;

/**
 * Updates the time to live (TTL) of a Rapid Cache instance.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $rapidCacheId The ID of the Rapid Cache.
 *        (e.g. 'us-central1-a')
 * @param int $ttlSeconds The new TTL for cache entries, in seconds. The TTL
 *        must be between 1 hour and 7 days.
 *        (e.g. 172800 for 2 days)
 */
function update_rapid_cache(string $bucketName, string $rapidCacheId, int $ttlSeconds): void
{
    $storageControlClient = new StorageControlClient();

    // Set project to "_" to signify global bucket
    $formattedName = $storageControlClient->rapidCacheName('_', $bucketName, $rapidCacheId);

    $rapidCache = new RapidCache([
        'name' => $formattedName,
        'ttl' => new Duration(['seconds' => $ttlSeconds]),
    ]);

    // Only the fields listed in the update mask are changed.
    $updateMask = new FieldMask([
        'paths' => ['ttl'],
    ]);

    $request = new UpdateRapidCacheRequest([
        'rapid_cache' => $rapidCache,
        'update_mask' => $updateMask,
    ]);

    // Updating a Rapid Cache is a long-running operation. This sample blocks
    // until it completes.
    $operation = $storageControlClient->updateRapidCache($request);
    $operation->pollUntilComplete();

    /** @var RapidCache $rapidCacheResult */
    $rapidCacheResult = $operation->getResult();
    printf('Updated Rapid Cache: %s' . PHP_EOL, $rapidCacheResult->getName());
    printf('TTL: %d seconds' . PHP_EOL, $rapidCacheResult->getTtl()->getSeconds());
}
# [END storage_control_update_rapid_cache]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
