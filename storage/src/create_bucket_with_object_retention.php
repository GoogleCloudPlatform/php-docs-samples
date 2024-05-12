<?php
/**
 * Copyright 2024 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_create_bucket_with_object_retention]
use Google\Cloud\Storage\StorageClient;

/**
 * Create a Cloud Storage bucket with the object retention enabled.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 */
function create_bucket_with_object_retention(string $bucketName): void
{
    $storage = new StorageClient();

    $bucket = $storage->createBucket($bucketName, [
        'enableObjectRetention' => true
    ]);
    printf(
        'Created bucket %s with object retention enabled setting: %s' . PHP_EOL,
        $bucketName,
        $bucket->info()['objectRetention']['mode']
    );
}
# [END storage_create_bucket_with_object_retention]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
