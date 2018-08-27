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

use Silex\Application;
use Silex\Provider\TwigServiceProvider;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('http.html.twig');
});

$app->post('/request/file', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    # [START gae_issue_request_http_bin]
    $url = 'http://httpbin.org/post?query=update';
    $data = ['data' => 'this', 'data2' => 'that'];
    $headers = "accept: */*\r\n" .
        "Content-Type: application/x-www-form-urlencoded\r\n" .
        "Custom-Header: custom-value\r\n" .
        "Custom-Header-Two: custom-value-2\r\n";

    $context = [
        'http' => [
            'method' => 'POST',
            'header' => $headers,
            'content' => http_build_query($data),
        ]
    ];
    $context = stream_context_create($context);
    $result = file_get_contents($url, false, $context);
    # [END gae_issue_request_http_bin]
    return $twig->render('http.html.twig', ['file_result' => $result]);
});

$app->post('/request/curl', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    // make sure one of the extensions is installed
    if (!function_exists('curl_init')) {
        throw new \Exception('You must enable cURL or cURLite in php.ini');
    }

    # [START gae_issue_request_curl_request]
    $url = 'http://httpbin.org/post?query=update';
    $data = ['data' => 'this', 'data2' => 'that'];
    $headers = [
        'Accept: */*',
        'Content-Type: application/x-www-form-urlencoded',
        'Custom-Header: custom-value',
        'Custom-Header-Two: custom-value-2'
    ];

    // open connection
    $ch = curl_init();

    // set curl options
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_POST => count($data),
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
    ];
    curl_setopt_array($ch, $options);

    // execute
    $result = curl_exec($ch);

    // close connection
    curl_close($ch);
    # [END gae_issue_request_curl_request]
    return $twig->render('http.html.twig', ['curl_result' => $result]);
});

$app->post('/request/guzzle', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    # [START gae_issue_request_guzzle_request]
    $url = 'http://httpbin.org/post?query=update';
    $data = ['data' => 'this', 'data2' => 'that'];
    $headers = [
        'Accept' => '*/*',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'Custom-Header' => 'custom-value',
        'Custom-Header-Two' => 'custom-value',
    ];

    $guzzle = new GuzzleHttp\Client;
    $request = new GuzzleHttp\Psr7\Request('POST', $url, $headers, http_build_query($data));
    $result = $guzzle->send($request);
    # [END gae_issue_request_guzzle_request]

    return $twig->render('http.html.twig', ['guzzle_result' => $result->getBody()]);
});

return $app;
