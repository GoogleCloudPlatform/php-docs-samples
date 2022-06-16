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

# [START storage_stream_file_upload]
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\WriteStream;

/**
 * Upload a chunked file stream.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 * @param string $source The path to the file to upload.
 */
function upload_object_stream($bucketName, $objectName, $source)
{
    // $bucketName = 'my-bucket';
    // $objectName = 'my-object';
    // $source = '/path/to/your/file';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $writeStream = new WriteStream(null, [
        'chunkSize' => 1024 * 256, // 256KB
    ]);
    $uploader = $bucket->getStreamableUploader($writeStream, [
        'name' => $objectName
    ]);
    $writeStream->setUploader($uploader);
    $file = fopen($source, 'r');
    while (($line = stream_get_line($file, 1024 * 256)) !== false) {
        $writeStream->write($line);
    }
    $writeStream->close();
    fclose($file);

    printf('Uploaded %s to gs://%s/%s' . PHP_EOL, basename($source), $bucketName, $objectName);
}
# [END storage_stream_file_upload]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
