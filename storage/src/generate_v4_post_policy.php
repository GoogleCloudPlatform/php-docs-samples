<?php
/**
 * Copyright 2020 Google LLC
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

# [START storage_generate_signed_post_policy_v4]
use Google\Cloud\Storage\StorageClient;

/**
 * Generates a V4 POST Policy to be used in an HTML form and echo's form.
 *
 * @param string $bucketName the name of your Google Cloud bucket.
 * @param string $objectName the name of your Google Cloud object.
 *
 * @return void
 */
function generate_v4_post_policy($bucketName, $objectName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    
    $response = $bucket->generateSignedPostPolicyV4(
        $objectName,
        new \DateTime('10 min'),
        [
            'fields' => [
                'x-goog-meta-test' => 'data'
            ]
        ]
    );

    $url = $response['url'];
    $output = "<form action='$url' method='POST' enctype='multipart/form-data'>" . PHP_EOL;
    foreach ($response['fields'] as $name => $value) {
        $output .= "  <input name='$name' value='$value' type='hidden'/>" . PHP_EOL;
    }
    $output .= "  <input type='file' name='file'/><br />" . PHP_EOL;
    $output .= "  <input type='submit' value='Upload File' name='submit'/><br />" . PHP_EOL;
    $output .= "</form>" . PHP_EOL;

    echo $output;
}
# [END storage_generate_signed_post_policy_v4]
