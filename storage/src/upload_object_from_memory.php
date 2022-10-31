<?php
/**
 * Copyright 2022 Google Inc.
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

# [START storage_file_upload_from_memory]
use Google\Cloud\Storage\StorageClient;

/**
 * Upload an object from memory buffer.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 * @param string $contents The contents to upload to the file.
 */
function upload_object_from_memory(
    string $bucketName,
    string $objectName,
    string $contents
): void {
    // $bucketName = 'my-bucket';
    // $objectName = 'my-object';
    // $contents = 'these are my contents';

    $storage = new StorageClient();
    $stream = fopen('data://text/plain,' . $contents, 'r');
    $bucket = $storage->bucket($bucketName);
    $bucket->upload($stream, [
        'name' => $objectName,
    ]);
    printf('Uploaded %s to gs://%s/%s' . PHP_EOL, $contents, $bucketName, $objectName);
}
# [END storage_file_upload_from_memory]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
