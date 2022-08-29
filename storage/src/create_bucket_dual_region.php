<?php
/**
 * Copyright 2022 Google LLC
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_create_bucket_dual_region]
use Google\Cloud\Storage\StorageClient;

/**
 * Create a new bucket with a custom default storage class and location.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $location Location for the bucket's regions. Case-insensitive.
 * @param string $region1 First region for the bucket's regions. Case-insensitive.
 * @param string $region2 Second region for the bucket's regions. Case-insensitive.
 */
function create_bucket_dual_region($bucketName, $location, $region1, $region2)
{
    // $bucketName = 'my-bucket';
    // $location = 'US';
    // $region1 = 'US-EAST1';
    // $region2 = 'US-WEST1';

    $storage = new StorageClient();
    $bucket = $storage->createBucket($bucketName, [
        'location' => $location,
        'customPlacementConfig' => [
            'dataLocations' => [$region1, $region2],
        ],
    ]);

    $info = $bucket->info();

    printf("Created '%s':", $bucket->name());
    printf("- location: '%s'", $info['location']);
    printf("- locationType: '%s'", $info['locationType']);
    printf("- customPlacementConfig: '%s'" . PHP_EOL, print_r($info['customPlacementConfig'], true));
}
# [END storage_create_bucket_dual_region]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
