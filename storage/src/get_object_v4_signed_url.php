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

# [START storage_generate_signed_url_v4]
use Google\Cloud\Storage\StorageClient;

/**
 * Generate a v4 signed URL for downloading an object.
 *
 * @param string $bucketName the name of your Google Cloud bucket.
 * @param string $objectName the name of your Google Cloud object.
 *
 * @return void
 */
function get_object_v4_signed_url($bucketName, $objectName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $url = $object->signedUrl(
        # This URL is valid for 15 minutes
        new \DateTime('15 min'),
        [
            'version' => 'v4',
        ]
    );

    print('Generated GET signed URL:' . PHP_EOL);
    print($url . PHP_EOL);
    print('You can use this URL with any user agent, for example:' . PHP_EOL);
    print('curl ' . $url . PHP_EOL);
}
# [END storage_generate_signed_url_v4]
