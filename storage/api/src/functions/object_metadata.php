<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/storage/api/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START object_metadata]
use Google\Cloud\Storage\StorageClient;

/**
 * Add ACL to a Cloud Storage Bucket.
 *
 * @param string $projectId the project ID of your project
 *
 * @return void
 */
function object_metadata($bucketName, $objectName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $info = $object->info();
    printf('Blob: %s' . PHP_EOL, $info['name']);
    printf('Bucket: %s' . PHP_EOL, $info['bucket']);
    printf('Storage class: %s' . PHP_EOL, $info['storageClass']);
    printf('ID: %s' . PHP_EOL, $info['id']);
    printf('Size: %s' . PHP_EOL, $info['size']);
    printf('Updated: %s' . PHP_EOL, $info['updated']);
    printf('Generation: %s' . PHP_EOL, $info['generation']);
    printf('Metageneration: %s' . PHP_EOL, $info['metageneration']);
    printf('Etag: %s' . PHP_EOL, $info['etag']);
    printf('Crc32c: %s' . PHP_EOL, $info['crc32c']);
    printf('MD5 Hash: %s' . PHP_EOL, $info['md5Hash']);
    printf('Content-type: %s' . PHP_EOL, $info['contentType']);
    if (isset($info['metadata'])) {
        printf('Metadata: %s', print_r($info['metadata'], true));
    }
}
# [END object_metadata]
