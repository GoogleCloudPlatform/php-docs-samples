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

# [START storage_delete_file]
use Google\Cloud\Storage\StorageClient;

/**
 * Delete an object.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $objectName the name of your Cloud Storage object.
 * @param array $options
 *
 * @return void
 */
function delete_object($bucketName, $objectName, $options = [])
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $object->delete();
    printf('Deleted gs://%s/%s' . PHP_EOL, $bucketName, $objectName);
}
# [END storage_delete_file]
