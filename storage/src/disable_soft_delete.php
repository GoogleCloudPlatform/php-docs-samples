<?php
/**
 * Copyright 2025 Google Inc.
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

# [START storage_disable_soft_delete]
use Google\Cloud\Storage\StorageClient;

/**
 * Disable bucket's soft delete.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 */
function disable_soft_delete(string $bucketName): void
{
    try {
        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $x = $bucket->update([
            'softDeletePolicy' => [
                'retentionDurationSeconds' => 0,
            ],
        ]);
        printf('Bucket %s soft delete policy was disabled' . PHP_EOL, $bucketName);
    } catch (\Throwable $th) {
        print_r($th);
    }

}
# [END storage_disable_soft_delete]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
