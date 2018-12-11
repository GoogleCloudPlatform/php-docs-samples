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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/auth/README.md
 */

# [START gae_auth_cloud_implicit]
namespace Google\Cloud\Samples\Auth;

// Imports the Cloud Storage client library.
use Google\Cloud\Storage\StorageClient;

function auth_cloud($projectId)
{
    # If you don't specify credentials when constructing the client, the
    # client library will look for credentials in the environment.
    $storage = new StorageClient([
        'projectId' => $projectId
    ]);

    # Make an authenticated API request (listing storage buckets)
    foreach ($storage->buckets() as $bucket) {
        printf('Bucket: %s' . PHP_EOL, $bucket->name());
    }
}
# [END gae_auth_cloud_implicit]
