<?php
/**
 * Copyright 2016 Google Inc.
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

# [START storage_move_file]
use Google\Cloud\Storage\StorageClient;

/**
 * Move an object to a new name and/or bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * (e.g. 'my-bucket')
 * @param string $objectName The name of your Cloud Storage object.
 * (e.g. 'my-object')
 * @param string $newBucketName the destination bucket name.
 * (e.g. 'my-other-bucket')
 * @param string $newObjectName the destination object name.
 * (e.g. 'my-other-object')
 */
function move_object(string $bucketName, string $objectName, string $newBucketName, string $newObjectName): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $object->copy($newBucketName, ['name' => $newObjectName]);
    $object->delete();
    printf('Moved gs://%s/%s to gs://%s/%s' . PHP_EOL,
        $bucketName,
        $objectName,
        $newBucketName,
        $newObjectName);
}
# [END storage_move_file]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
