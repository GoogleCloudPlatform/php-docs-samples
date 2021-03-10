<?php
/**
 * Copyright 2021 Google LLC
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

# [START storage_get_public_access_prevention]
use Google\Cloud\Storage\StorageClient;

/**
 * Get the Public Access Prevention setting for a bucket
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 */
function get_bucket_public_access_prevention($bucketName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $iamConfiguration = $bucket->info()['iamConfiguration'];

    printf(
        'The bucket public access prevention is %s for %s.' . PHP_EOL,
        $iamConfiguration['publicAccessPrevention'],
        $bucketName
    );
}
# [END storage_get_public_access_prevention]
