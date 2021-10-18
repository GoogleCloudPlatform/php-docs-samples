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

# [START storage_cors_configuration]
use Google\Cloud\Storage\StorageClient;

/**
 * Update the CORS configuration of a bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $method The HTTP method for the CORS config.
 * @param string $origin The origin from which the CORS config will allow requests.
 * @param string $responseHeader The response header to share across origins.
 * @param int $maxAgeSeconds The maximum amount of time the browser can make
 *     requests before it must repeat preflighted requests.
 */
function cors_configuration($bucketName, $method, $origin, $responseHeader, $maxAgeSeconds)
{
    // $bucketName = 'my-bucket';
    // $method = 'GET';
    // $origin = 'http://example.appspot.com';
    // $responseHeader = 'Content-Type';
    // $maxAgeSeconds = 3600;

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $bucket->update([
        'cors' => [
            [
                'method' => [$method],
                'origin' => [$origin],
                'responseHeader' => [$responseHeader],
                'maxAgeSeconds' => $maxAgeSeconds,
            ]
        ]
    ]);

    printf(
        'Bucket %s was updated with a CORS config to allow GET requests from ' .
        '%s sharing %s responses across origins.',
        $bucketName,
        $origin,
        $responseHeader
    );
}
# [END storage_cors_configuration]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
