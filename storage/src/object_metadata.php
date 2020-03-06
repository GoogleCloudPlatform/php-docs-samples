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

# [START storage_get_metadata]
use Google\Cloud\Storage\StorageClient;

/**
 * List object metadata.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $objectName the name of your Cloud Storage object.
 *
 * @return void
 */
function object_metadata($bucketName, $objectName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $info = $object->info();
    if (isset($info['name'])) {
        printf('Blob: %s' . PHP_EOL, $info['name']);
    }
    if (isset($info['bucket'])) {
        printf('Bucket: %s' . PHP_EOL, $info['bucket']);
    }
    if (isset($info['storageClass'])) {
        printf('Storage class: %s' . PHP_EOL, $info['storageClass']);
    }
    if (isset($info['id'])) {
        printf('ID: %s' . PHP_EOL, $info['id']);
    }
    if (isset($info['size'])) {
        printf('Size: %s' . PHP_EOL, $info['size']);
    }
    if (isset($info['updated'])) {
        printf('Updated: %s' . PHP_EOL, $info['updated']);
    }
    if (isset($info['generation'])) {
        printf('Generation: %s' . PHP_EOL, $info['generation']);
    }
    if (isset($info['metageneration'])) {
        printf('Metageneration: %s' . PHP_EOL, $info['metageneration']);
    }
    if (isset($info['etag'])) {
        printf('Etag: %s' . PHP_EOL, $info['etag']);
    }
    if (isset($info['crc32c'])) {
        printf('Crc32c: %s' . PHP_EOL, $info['crc32c']);
    }
    if (isset($info['md5Hash'])) {
        printf('MD5 Hash: %s' . PHP_EOL, $info['md5Hash']);
    }
    if (isset($info['contentType'])) {
        printf('Content-type: %s' . PHP_EOL, $info['contentType']);
    }
    if (isset($info['temporaryHold'])) {
        printf("Temporary hold: " . ($info['temporaryHold'] ? "enabled" : "disabled") . PHP_EOL);
    }
    if (isset($info['eventBasedHold'])) {
        printf("Event-based hold: " . ($info['eventBasedHold'] ? "enabled" : "disabled") . PHP_EOL);
    }
    if (isset($info['retentionExpirationTime'])) {
        printf("retentionExpirationTime: " . $info['retentionExpirationTime'] . PHP_EOL);
    }
    if (isset($info['metadata'])) {
        printf('Metadata: %s', print_r($info['metadata'], true));
    }
}
# [END storage_get_metadata]
