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

# [START storage_set_retention_policy]
use Google\Cloud\Storage\StorageClient;

/**
 * Sets a bucket's retention policy.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param int $retentionPeriod The retention period for objects in bucket, in seconds.
 */
function set_retention_policy(string $bucketName, int $retentionPeriod): void
{
    // $bucketName = 'my-bucket';
    // $retentionPeriod = 3600;

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $bucket->update([
        'retentionPolicy' => [
            'retentionPeriod' => $retentionPeriod
        ]]);
    printf('Bucket %s retention period set to %s seconds' . PHP_EOL, $bucketName,
        $retentionPeriod);
}
# [END storage_set_retention_policy]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
