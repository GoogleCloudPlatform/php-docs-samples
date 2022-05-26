<?php
/**
 * Copyright 2017 Google Inc.
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

# [START auth_api_explicit]
namespace Google\Cloud\Samples\Auth;

use Google\Client;
use Google\Service\Storage;

/**
 * Authenticate to a cloud API using a service account explicitly.
 *
 * @param string $projectId           The Google project ID.
 * @param string $serviceAccountPath  Path to service account credentials JSON.
 */
function auth_api_explicit($projectId, $serviceAccountPath)
{
    $client = new Client();
    $client->setAuthConfig($serviceAccountPath);
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');

    $storage = new Storage($client);

    # Make an authenticated API request (listing storage buckets)
    $buckets = $storage->buckets->listBuckets($projectId);

    foreach ($buckets['items'] as $bucket) {
        printf('Bucket: %s' . PHP_EOL, $bucket->getName());
    }
}
# [END auth_api_explicit]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
