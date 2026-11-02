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

# [START storage_control_create_rapid_cache]
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\CreateRapidCacheRequest;
use Google\Cloud\Storage\Control\V2\RapidCache;

/**
 * Creates a Rapid Cache instance.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $zone The zone in which to create the Rapid Cache. The zone
 *        must be in the same region as the bucket.
 *        (e.g. 'us-central1-a')
 */
function create_rapid_cache(string $bucketName, string $zone): void
{
    $storageControlClient = new StorageControlClient();

    // Set project to "_" to signify global bucket
    $formattedName = $storageControlClient->bucketName('_', $bucketName);

    // Rapid Cache Ultra requires a bucket with hierarchical namespace enabled.
    $rapidCache = new RapidCache([
        'zone' => $zone,
        'cache_type' => 'rapid-cache-ultra',
    ]);

    $request = new CreateRapidCacheRequest([
        'parent' => $formattedName,
        'rapid_cache' => $rapidCache,
    ]);

    // Creating a Rapid Cache is a long-running operation. This sample blocks
    // until it completes.
    $operation = $storageControlClient->createRapidCache($request);
    $operation->pollUntilComplete();

    /** @var RapidCache $rapidCacheResult */
    $rapidCacheResult = $operation->getResult();
    printf('Created Rapid Cache: %s' . PHP_EOL, $rapidCacheResult->getName());
}
# [END storage_control_create_rapid_cache]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
