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

# [START gae_auth_api_implicit]
namespace Google\Cloud\Samples\Auth;

// Imports the Cloud Storage client library.
use Google_Client;
use Google_Service_Storage;

function auth_api($projectId)
{
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    $storage = new Google_Service_Storage($client);

    # Make an authenticated API request (listing storage buckets)
    $buckets = $storage->buckets->listBuckets($projectId);

    foreach ($buckets['items'] as $bucket) {
        printf('Bucket: %s' . PHP_EOL, $bucket->getName());
    }
}
# [END gae_auth_api_implicit]
