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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_upload_file]
use Google\Cloud\Storage\StorageClient;

/**
 * Upload a file.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $objectName The name of your Cloud Storage object.
 *        (e.g. 'my-object')
 * @param string $source The path to the file to upload.
 *        (e.g. '/path/to/your/file')
 */
function upload_object(string $bucketName, string $objectName, string $source): void
{
    $storage = new StorageClient();
    if (!$file = fopen($source, 'r')) {
        throw new \InvalidArgumentException('Unable to open file for reading');
    }
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->upload($file, [
        'name' => $objectName
    ]);
    printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);
}
# [END storage_upload_file]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
