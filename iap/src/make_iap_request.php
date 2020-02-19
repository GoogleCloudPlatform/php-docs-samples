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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/iap/README.md
 */

# [START iap_make_request]
namespace Google\Cloud\Samples\Iap;

# Imports Auth libraries and Guzzle HTTP libraries.
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

/**
 * Make a request to an application protected by Identity-Aware Proxy.
 *
 * @param string $url The Identity-Aware Proxy-protected URL to fetch.
 * @param string $clientId The client ID used by Identity-Aware Proxy.
 *
 * @return The response body.
 */
function make_iap_request($url, $clientId)
{
    // create middleware, using the client ID as the target audience for IAP
    $middleware = ApplicationDefaultCredentials::getIdTokenMiddleware($clientId);
    $stack = HandlerStack::create();
    $stack->push($middleware);

    // create the HTTP client
    $client = new Client([
        'handler' => $stack,
        'auth' => 'google_auth'
    ]);

    // make the request
    return $client->get($url);
}
# [END iap_make_request]
