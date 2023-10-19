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

# [START storage_set_autoclass]
use Google\Cloud\Storage\StorageClient;

/**
 * Updates an existing bucket with provided autoclass config.
 *
 * @param string $bucketName The name of your Cloud Storage bucket (e.g. 'my-bucket').
 * @param bool $autoclassStatus If true, enables Autoclass. Disables otherwise.
 * @param string $terminalStorageClass This field is optional and defaults to `NEARLINE`.
 *        Valid values are `NEARLINE` and `ARCHIVE`.
 */
function set_bucket_autoclass(
    string $bucketName,
    bool $autoclassStatus,
    string $terminalStorageClass
): void {
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $bucket->update([
        'autoclass' => [
            'enabled' => $autoclassStatus,
            'terminalStorageClass' => $terminalStorageClass
        ],
    ]);

    $info = $bucket->info();
    printf(
        'Updated bucket %s with autoclass set to %s.' . PHP_EOL,
        $info['name'],
        $autoclassStatus ? 'true' : 'false'
    );
    printf(
        'Autoclass terminal storage class is %s.' . PHP_EOL,
        $info['autoclass']['$terminalStorageClass']
    );
}
# [END storage_set_autoclass]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
