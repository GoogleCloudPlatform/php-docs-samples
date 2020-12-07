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

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    // Simple echo service.
    $url = 'https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/endpoints/getting-started/README.md';

    $welcome = sprintf(
        '<h1>Welcome to the Endpoints getting started tutorial!</h1>' .
        '<p>Please see the <a href="%s">README</a> for instructions</p>',
        $url
    );
    return $welcome;
});

$app->post('/echo', function (Request $request) use ($app) {
    // Simple echo service.
    $message = $request->get('message');
    return $app->json(['message' => $message]);
});

$app->get('/auth/info/googlejwt', function () use ($app) {
    // Auth info with Google signed JWT.
    return $app->json($app['auth_info']);
});


$app->get('/auth/info/googleidtoken', function () use ($app) {
    // Auth info with Google ID token.
    return $app->json($app['auth_info']);
});

$app['auth_info'] = function (Request $request) use ($app) {
    // Retrieves the authenication information from Google Cloud Endpoints.
    $encoded_info = $request->headers->get('X-Endpoint-API-UserInfo');

    if ($encoded_info) {
        $info_json = utf8_decode(base64_decode($encoded_info));
        $user_info = json_decode($info_json);
    } else {
        $user_info = ['id' => 'anonymous'];
    }

    return $user_info;
};

// Accept JSON requests
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

return $app;
