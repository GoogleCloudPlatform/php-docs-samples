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

# [START storage_upload_encrypted_file]
use Google\Cloud\Storage\StorageClient;

/**
 * Upload an encrypted file.
 *
 * @param string $bucketName the name of your Google Cloud bucket.
 * @param string $objectName the name of your Google Cloud object.
 * @param resource $source the path to the file to upload.
 * @param string $base64EncryptionKey the base64 encoded encryption key.
 *
 * @return void
 */
function upload_encrypted_object($bucketName, $objectName, $source, $base64EncryptionKey)
{
    $storage = new StorageClient();
    $file = fopen($source, 'r');
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->upload($file, [
        'name' => $objectName,
        'encryptionKey' => $base64EncryptionKey,
    ]);
    printf('Uploaded encrypted %s to gs://%s/%s' . PHP_EOL,
        basename($source), $bucketName, $objectName);
}
# [END storage_upload_encrypted_file]
