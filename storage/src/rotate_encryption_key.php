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
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 * @param string $oldBase64EncryptionKey The Base64 encoded AES-256 encryption
 *     key originally used to encrypt the object. See the documentation on
 *     Customer-Supplied Encryption keys for more info:
 *     https://cloud.google.com/storage/docs/encryption/using-customer-supplied-keys
 * @param string $newBase64EncryptionKey The new base64 encoded encryption key.
 */
function rotate_encryption_key(
    $bucketName,
    $objectName,
    $oldBase64EncryptionKey,
    $newBase64EncryptionKey
) {
    // $bucketName = 'my-bucket';
    // $objectName = 'my-object';
    // $oldbase64EncryptionKey = 'TIbv/fjexq+VmtXzAlc63J4z5kFmWJ6NdAPQulQBT7g=';
    // $newBase64EncryptionKey = '0mMWhFvQOdS4AmxRpo8SJxXn5MjFhbz7DkKBUdUIef8=';

    $storage = new StorageClient();
    $object = $storage->bucket($bucketName)->object($objectName);

    $rewrittenObject = $object->rewrite($bucketName, [
        'encryptionKey' => $oldBase64EncryptionKey,
        'destinationEncryptionKey' => $newBase64EncryptionKey,
    ]);

    printf('Rotated encryption key for object gs://%s/%s' . PHP_EOL,
        $bucketName, $objectName);
}
# [END storage_rotate_encryption_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
