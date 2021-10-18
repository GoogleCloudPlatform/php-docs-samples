<?php
/**
 * Copyright 2016 Google Inc.
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
 * Google Cloud Endpoints sample application.
 *
 * Demonstrates how to create a simple echo API as well as how to deal with
 * various authentication methods.
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    // Simple echo service.
    $url = 'https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/endpoints/getting-started/README.md';

    $response->getBody()->write(sprintf(
        '<h1>Welcome to the Endpoints getting started tutorial!</h1>' .
        '<p>Please see the <a href="%s">README</a> for instructions</p>',
        $url
    ));

    return $response;
});

$app->post('/echo', function (Request $request, Response $response) use ($app) {
    // Simple echo service.
    $json = json_decode((string) $request->getBody(), true);
    $response->getBody()->write(json_encode([
        'message' => $json['message'] ?? '',
    ]));
    return $response
        ->withHeader('Content-Type', 'application/json');
});

$app->get('/auth/info/googlejwt', function (Request $request, Response $response) {
    // Auth info with Google signed JWT.
    $userInfo = get_user_info($request);
    $response->getBody()->write(json_encode($userInfo));
    return $response
        ->withHeader('Content-Type', 'application/json');
});

$app->get('/auth/info/googleidtoken', function (Request $request, Response $response) {
    // Auth info with Google ID token.
    $userInfo = get_user_info($request);
    $response->getBody()->write(json_encode($userInfo));
    return $response
        ->withHeader('Content-Type', 'application/json');
});

function get_user_info(Request $request)
{
    // Retrieves the authenication information from Google Cloud Endpoints.
    $encoded_info = $request->getHeaderLine('X-Endpoint-API-UserInfo');

    if ($encoded_info) {
        $info_json = utf8_decode(base64_decode($encoded_info));
        $user_info = json_decode($info_json);
    } else {
        $user_info = ['id' => 'anonymous'];
    }

    return $user_info;
}

return $app;
