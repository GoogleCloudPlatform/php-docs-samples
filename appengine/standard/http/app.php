<?php
/**
 * Copyright 2015 Google Inc.
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

$app->post('/http_request', function () use ($app) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    # [START http_bin]
    $data = ['data' => 'this', 'data2' => 'that'];
    $postData = http_build_query($data);
    $context = [
        'http' => [
            'method' => 'POST',
            'header' => "accept: */*\r\n" .
                        "content-type: application/x-www-form-urlencoded\r\n" .
                        "custom-header: custom-value\r\n" .
                        "custom-header-two: custom-value-2\r\n",
            'content' => $postData
        ]
    ];
    $context = stream_context_create($context);
    $result = file_get_contents('http://httpbin.org/post?query=update', false, $context);
    # [END http_bin]
    return $twig->render('http.html.twig', ['result' => $result]);
});

return $app;
