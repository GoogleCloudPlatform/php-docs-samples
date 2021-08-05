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

# [START storage_upload_with_kms_key]
use Google\Cloud\Storage\StorageClient;

/**
 * Upload a file using KMS encryption.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 * @param string $source The path to the file to upload.
 * @param string $kmsKeyName The KMS key used to encrypt objects server side.
 *     Key names are provided in the following format:
 *     `projects/<PROJECT>/locations/<LOCATION>/keyRings/<RING_NAME>/cryptoKeys/<KEY_NAME>`.
 */
function upload_with_kms_key($bucketName, $objectName, $source, $kmsKeyName)
{
    // $bucketName = 'my-bucket';
    // $objectName = 'my-object';
    // $source = '/path/to/your/file';
    // $kmsKeyName = "";

    $storage = new StorageClient();
    $file = fopen($source, 'r');
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->upload($file, [
        'name' => $objectName,
        'destinationKmsKeyName' => $kmsKeyName,
    ]);
    printf('Uploaded %s to gs://%s/%s using encryption key %s' . PHP_EOL,
        basename($source),
        $bucketName,
        $objectName,
        $kmsKeyName);
}
# [END storage_upload_with_kms_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
