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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_get_autoclass]
use Google\Cloud\Storage\StorageClient;

/**
 * Print a bucket autoclass configuration.
 *
 * @param string $bucketName The name of your Cloud Storage bucket (e.g. 'my-bucket').
 */
function get_bucket_autoclass(string $bucketName): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $info = $bucket->info();

    if (isset($info['autoclass'])) {
        printf(
            'Bucket %s has autoclass enabled: %s' . PHP_EOL,
            $bucketName,
            $info['autoclass']['enabled']
        );
        printf(
            'Bucket %s has autoclass toggle time: %s' . PHP_EOL,
            $bucketName,
            $info['autoclass']['toggleTime']
        );
        printf(
            'Autoclass terminal storage class is set to %s for %s at %s.' . PHP_EOL,
            $info['autoclass']['terminalStorageClass'],
            $info['name'],
            $info['autoclass']['terminalStorageClassUpdateTime'],
        );
    }
}
# [END storage_get_autoclass]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
