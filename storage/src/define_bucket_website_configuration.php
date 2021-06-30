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

# [START storage_define_bucket_website_configuration]
use Google\Cloud\Storage\StorageClient;

/**
 * Update the given bucket's website configuration.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $indexPageObject the name of an object in the bucket to use as
 *     an index page for a static website bucket.
 * @param string $notFoundPageObject the name of an object in the bucket to use
 *     as the 404 Not Found page.
 */
function define_bucket_website_configuration($bucketName, $indexPageObject, $notFoundPageObject)
{
    // $bucketName = 'my-bucket';
    // $indexPageObject = 'index.html';
    // $notFoundPageObject = '404.html';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $bucket->update([
        'website' => [
            'mainPageSuffix' => $indexPageObject,
            'notFoundPage' => $notFoundPageObject
        ]
    ]);

    printf(
        'Static website bucket %s is set up to use %s as the index page and %s as the 404 page.',
        $bucketName,
        $indexPageObject,
        $notFoundPageObject
    );
}
# [END storage_define_bucket_website_configuration]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
