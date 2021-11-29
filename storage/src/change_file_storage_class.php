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

# [START storage_change_file_storage_class]
use Google\Cloud\Storage\StorageClient;

/**
 * Change the storage class of the given file.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 * @param string $storageClass The storage class of the new object.
 */
function change_file_storage_class($bucketName, $objectName, $storageClass)
{
    // $bucketName = 'my-bucket';
    // $objectName = 'my-object';
    // $storageClass = 'COLDLINE';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);

    // Storage class cannot be changed directly. But we can rewrite the object
    // using the new storage class.

    $newObject = $object->rewrite($bucket, [
        'storageClass' => $storageClass,
    ]);

    printf(
        'Object %s in bucket %s had its storage class set to %s',
        $objectName,
        $bucketName,
        $newObject->info()['storageClass']
    );
}
# [END storage_change_file_storage_class]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
