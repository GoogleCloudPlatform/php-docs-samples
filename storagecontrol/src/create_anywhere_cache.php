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

# [START storage_control_create_anywhere_cache]
use Google\Cloud\Storage\Control\V2\AnywhereCache;
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\CreateAnywhereCacheRequest;

/**
 * Creates an Anywhere Cache instance.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $zone The zone in which the cache instance is running.
 *        (e.g. 'us-east1-b')
 */
function create_anywhere_cache(string $bucketName, string $zone): void
{
    $storageControlClient = new StorageControlClient();

    // Set project to "_" to signify global bucket
    $formattedName = $storageControlClient->bucketName('_', $bucketName);

    $anywhereCache = new AnywhereCache([
        'zone' => $zone,
    ]);

    $request = new CreateAnywhereCacheRequest([
        'parent' => $formattedName,
        'anywhere_cache' => $anywhereCache,
    ]);

    // Start a create operation and block until it completes. Real applications
    // may want to setup a callback, wait on a coroutine, or poll until it
    // completes.
    $response = $storageControlClient->createAnywhereCache($request);

    printf('Created anywhere cache: %s', $response->getName());
}
# [END storage_control_create_anywhere_cache]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
