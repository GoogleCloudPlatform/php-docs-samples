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

# [START storage_get_retention_policy]
use Google\Cloud\Storage\StorageClient;

/**
 * Gets a bucket's retention policy.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 */
function get_retention_policy($bucketName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $bucket->reload();

    printf('Retention Policy for ' . $bucketName . PHP_EOL);
    printf('Retention Period: ' . $bucket->info()['retentionPolicy']['retentionPeriod'] . PHP_EOL);
    if (array_key_exists('isLocked', $bucket->info()['retentionPolicy']) &&
        $bucket->info()['retentionPolicy']['isLocked']) {
        printf('Retention Policy is locked' . PHP_EOL);
    }
    if ($bucket->info()['retentionPolicy']['effectiveTime']) {
        printf('Effective Time: ' . $bucket->info()['retentionPolicy']['effectiveTime'] . PHP_EOL);
    }
}
# [END storage_get_retention_policy]
