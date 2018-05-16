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
use Google\Auth\OAuth2;
use Google\Auth\Middleware\ScopedAccessTokenMiddleware;
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
function make_iap_request($url, $clientId, $pathToServiceAccount)
{
    $serviceAccountKey = json_decode(file_get_contents($pathToServiceAccount), true);
    $oauth_token_uri = 'https://www.googleapis.com/oauth2/v4/token';
    $iam_scope = 'https://www.googleapis.com/auth/iam';

    # Create an OAuth object using the service account key
    $oauth = new OAuth2([
        'audience' => $oauth_token_uri,
        'issuer' => $serviceAccountKey['client_email'],
        'signingAlgorithm' => 'RS256',
        'signingKey' => $serviceAccountKey['private_key'],
        'tokenCredentialUri' => $oauth_token_uri,
    ]);
    $oauth->setGrantType(OAuth2::JWT_URN);
    $oauth->setAdditionalClaims(['target_audience' => $clientId]);

    # Obtain an OpenID Connect token, which is a JWT signed by Google.
    $token = $oauth->fetchAuthToken();
    $idToken = $oauth->getIdToken();

    # Construct a ScopedAccessTokenMiddleware with the ID token.
    $middleware = new ScopedAccessTokenMiddleware(
        function () use ($idToken) {
            return $idToken;
        },
        $iam_scope
    );

    $stack = HandlerStack::create();
    $stack->push($middleware);

    # Create an HTTP Client using Guzzle and pass in the credentials.
    $http_client = new Client([
        'handler' => $stack,
        'base_uri' => $url,
        'auth' => 'scoped'
    ]);

    # Make an authenticated HTTP Request
    $response = $http_client->request('GET', '/', []);
    return $response;
}
# [END iap_make_request]
