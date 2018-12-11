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

# [START auth_api_explicit_app_engine]
namespace Google\Cloud\Samples\Auth;

use Google\Auth\Credentials\AppIdentityCredentials;
use Google\Auth\Middleware\AuthTokenMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use Google_Client;
use Google_Service_Storage;

function auth_api_explicit_app_engine($projectId)
{
    # Learn more about scopes at https://cloud.google.com/storage/docs/authentication#oauth-scopes
    $scope = 'https://www.googleapis.com/auth/devstorage.read_only';
    $gaeCredentials = new AppIdentityCredentials($scope);
    $middleware = new AuthTokenMiddleware($gaeCredentials);
    $stack = HandlerStack::create();
    $stack->push($middleware);
    $http_client = new Client([
        'handler' => $stack,
        'base_uri' => 'https://www.googleapis.com/auth/cloud-platform',
        'auth' => 'google_auth'
    ]);

    $client = new Google_Client();
    $client->setHttpClient($http_client);

    $storage = new Google_Service_Storage($client);

    # Make an authenticated API request (listing storage buckets)
    $buckets = $storage->buckets->listBuckets($projectId);

    foreach ($buckets['items'] as $bucket) {
        printf('Bucket: %s' . PHP_EOL, $bucket->getName());
    }
}
# [END auth_api_explicit_app_engine]
