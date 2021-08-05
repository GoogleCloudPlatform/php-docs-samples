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

# [START storage_object_csek_to_cmek]
use Google\Cloud\Storage\StorageClient;

/**
 * Migrate an object from a Customer-Specified Encryption Key to a Customer-Managed
 * Encryption Key.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 * @param string $decryptionKey The Base64 encoded decryption key, which should
 *     be the same key originally used to encrypt the object.
 * @param string $kmsKeyName The name of the KMS key to manage this object.
 *     Key names are provided in the following format:
 *     `projects/<PROJECT>/locations/<LOCATION>/keyRings/<RING_NAME>/cryptoKeys/<KEY_NAME>`.
 */
function object_csek_to_cmek($bucketName, $objectName, $decryptionKey, $kmsKeyName)
{
    // $bucketName = 'my-bucket';
    // $objectName = 'my-object';
    // $decryptionKey = 'TIbv/fjexq+VmtXzAlc63J4z5kFmWJ6NdAPQulQBT7g=';
    // $kmsKeyName = "";

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $object = $bucket->object($objectName, [
        'encryptionKey' => $decryptionKey,
    ]);

    $object->rewrite($bucketName, [
        'destinationKmsKeyName' => $kmsKeyName,
    ]);

    printf(
        'Object %s in bucket %s is now managed by the KMS key %s instead of a customer-supplied encryption key',
        $objectName,
        $bucketName,
        $kmsKeyName
    );
}
# [END storage_object_csek_to_cmek]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
