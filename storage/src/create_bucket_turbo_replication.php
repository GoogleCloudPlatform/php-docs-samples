<?php
/**
 * Copyright 2021 Google Inc.
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

# [START storage_create_bucket_turbo_replication]
use Google\Cloud\Storage\StorageClient;

/**
 * Create a Cloud Storage bucket with the recovery point objective (RPO) set to `ASYNC_TURBO`.
 * The bucket must be a dual-region bucket for this setting to take effect.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $location The Dual-Region location where you want your bucket to reside (e.g. "US-CENTRAL1+US-WEST1").
                                           Read more at https://cloud.google.com/storage/docs/locations#location-dr
 */
function create_bucket_turbo_replication($bucketName, $location = 'nam4')
{
    // $bucketName = 'my-bucket';

    $storage = new StorageClient();
    $rpo = 'ASYNC_TURBO';

    // providing a location which is a dual-region location
    // makes sure the locationType is set to 'dual-region' implicitly
    // we can pass 'locationType' => 'dual-region'
    // to make it explicit
    $bucket = $storage->createBucket($bucketName, [
        'location' => $location,
        'rpo' => $rpo
    ]);
    printf('Bucket with recovery point objective (RPO) set to \'ASYNC_TURBO\' created: %s' . PHP_EOL, $bucket->name());
}
# [END storage_create_bucket_turbo_replication]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
