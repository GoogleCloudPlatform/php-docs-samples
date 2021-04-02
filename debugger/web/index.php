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
# [START debugger_agent]
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Debugger\Agent;

$agent = new Agent(['sourceRoot' => realpath('../')]);
# [END debugger_agent]

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// Create App
$app = AppFactory::create();

// Create Twig
$twig = Twig::create(__DIR__ . '/../views');

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Slim version: ' . Slim\App::VERSION);
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, $args) use ($twig) {
    return $twig->render($response, 'hello.html.twig', [
        'name' => $args['name']
    ]);
});

$app->run();
