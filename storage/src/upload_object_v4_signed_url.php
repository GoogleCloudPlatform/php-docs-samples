<?php
/**
 * Copyright 2019 Google LLC
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

# [START storage_generate_upload_signed_url_v4]
use Google\Cloud\Storage\StorageClient;

/**
 * Generate a v4 signed URL for uploading an object.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 */
function upload_object_v4_signed_url($bucketName, $objectName)
{
    // $bucketName = 'my-bucket';
    // $objectName = 'my-object';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $url = $object->signedUrl(
        # This URL is valid for 15 minutes
        new \DateTime('15 min'),
        [
            'method' => 'PUT',
            'contentType' => 'application/octet-stream',
            'version' => 'v4',
        ]
    );

    print('Generated PUT signed URL:' . PHP_EOL);
    print($url . PHP_EOL);
    print('You can use this URL with any user agent, for example:' . PHP_EOL);
    print("curl -X PUT -H 'Content-Type: application/octet-stream' " .
        '--upload-file my-file ' . $url . PHP_EOL);
}
# [END storage_generate_upload_signed_url_v4]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
