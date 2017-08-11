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

# [START auth_http_implicit]
namespace Google\Cloud\Samples\Auth;

# Imports Auth libraries and Guzzle HTTP libraries.
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

function auth_http_implicit($projectId)
{
    # Get the credentials and project ID from the environment using Google Auth
    # library's ApplicationDefaultCredentials class.
    $middleware = ApplicationDefaultCredentials::getMiddleware(
        'https://www.googleapis.com/auth/cloud-platform');
    $stack = HandlerStack::create();
    $stack->push($middleware);

    # Create a HTTP Client using Guzzle and pass in the credentials.
    $http_client = new Client([
        'handler' => $stack,
        'base_uri' => 'https://www.googleapis.com/storage/v1/',
        'auth' => 'google_auth'
    ]);

    # Make an authenticated API request (listing storage buckets)
    $query = ['project' => $projectId];
    $response = $http_client->request('GET', 'b', [
        'query' => $query
    ]);
    $body_content = json_decode((string) $response->getBody());
    foreach ($body_content->items as $item) {
        $bucket = $item->id;
        printf('Bucket: %s' . PHP_EOL, $bucket);
    }
}
# [END auth_http_implicit]
