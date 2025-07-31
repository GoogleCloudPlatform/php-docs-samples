<?php
/**
 * Copyright 2025 Google LLC
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

# [START storage_control_update_anywhere_cache]
use Google\Cloud\Storage\Control\V2\AnywhereCache;
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\UpdateAnywhereCacheMetadata;
use Google\Cloud\Storage\Control\V2\UpdateAnywhereCacheRequest;
use Google\Protobuf\FieldMask;

/**
 * Updates an Anywhere Cache instance.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $anywhereCacheId Uniquely identifies the cache.
 *        (e.g. 'us-east1-b')
 * @param string $admission_policy The cache's admission policy.
 *        (e.g. 'admit-on-first-miss')
 */
function update_anywhere_cache(string $bucketName, string $anywhereCacheId, string $admission_policy): void
{
    $storageControlClient = new StorageControlClient();

    // Set project to "_" to signify global bucket
    $formattedName = $storageControlClient->anywhereCacheName('_', $bucketName, $anywhereCacheId);

    $anywhereCache = new AnywhereCache([
        'name' => $formattedName,
        'admission_policy' => $admission_policy,
    ]);

    $updateMask = new FieldMask([
        'paths' => ['admission_policy'],
    ]);

    // Start an update operation and block until it completes. Real applications
    // may want to setup a callback, wait on a coroutine, or poll until it
    // completes.
    $request = new UpdateAnywhereCacheRequest([
        'anywhere_cache' => $anywhereCache,
        'update_mask' => $updateMask,
    ]);

    $operation = $storageControlClient->updateAnywhereCache($request);

    printf('Waiting for operation %s to complete...' . PHP_EOL, $operation->getName());
    $operation->pollUntilComplete();

    // var_dump($operation);exit;
    /** @var UpdateAnywhereCacheMetadata */
    $metadata = $operation->getMetadata();
    printf('Updated anywhere cache: %s', $metadata->getAnywhereCacheId());
}
# [END storage_control_update_anywhere_cache]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
