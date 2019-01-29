<?php
/**
 * Copyright 2018 Google Inc.
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

# [START storage_remove_retention_policy]
use Google\Cloud\Storage\StorageClient;

/**
 * Removes a bucket's retention policy.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 */
function remove_retention_policy($bucketName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $bucket->reload();

    if (array_key_exists('isLocked', $bucket->info()['retentionPolicy']) &&
        $bucket->info()['retentionPolicy']['isLocked']) {
        printf('Unable to remove retention period as retention policy is locked.' . PHP_EOL);
        return;
    }

    $bucket->update([
        'retentionPolicy' => []
    ]);
    printf('Removed bucket %s retention policy' . PHP_EOL, $bucketName);
}
# [END storage_remove_retention_policy]
