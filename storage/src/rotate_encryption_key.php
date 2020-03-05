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

# [START storage_rotate_encryption_key]
use Google\Cloud\Storage\StorageClient;

/**
 * Change the encryption key used to store an existing object.
 *
 * @param string $bucketName the name of your Google Cloud bucket.
 * @param string $objectName the name of your Google Cloud object.
 * @param string $base64EncryptionKey the base64 encoded encryption key.
 * @param string $newBase64EncryptionKey the new base64 encoded encryption key.
 *
 * @return void
 */
function rotate_encryption_key(
    $bucketName,
    $objectName,
    $base64EncryptionKey,
    $newBase64EncryptionKey
) {
    $storage = new StorageClient();
    $object = $storage->bucket($bucketName)->object($objectName);

    $rewrittenObject = $object->rewrite($bucketName, [
        'encryptionKey' => $base64EncryptionKey,
        'destinationEncryptionKey' => $newBase64EncryptionKey,
    ]);

    printf('Rotated encryption key for object gs://%s/%s' . PHP_EOL,
        $bucketName, $objectName);
}
# [END storage_rotate_encryption_key]
