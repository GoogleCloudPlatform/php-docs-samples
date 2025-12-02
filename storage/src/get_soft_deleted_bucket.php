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

# [START storage_get_soft_deleted_bucket]
use Google\Cloud\Storage\StorageClient;

/**
 * Get softDeleted bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 *
 * @param string $generation The generation of the bucket to restore.
 *        (e.g. '123456789')
 */
function get_soft_deleted_bucket(string $bucketName, string $generation): void
{
    $options = ['generation' => $generation, 'softDeleted' => true];
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $info = $bucket->info($options);

    printf('Bucket: %s' . PHP_EOL, $bucketName);
    printf('Generation: %s' . PHP_EOL, $info['generation']);
    printf('SoftDeleteTime: %s' . PHP_EOL, $info['softDeleteTime']);
    printf('HardDeleteTime: %s' . PHP_EOL, $info['hardDeleteTime']);
}
# [END storage_get_soft_deleted_bucket]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
