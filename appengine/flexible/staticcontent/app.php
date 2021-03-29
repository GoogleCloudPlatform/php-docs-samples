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

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use RKA\Middleware\IpAddress;

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

// Create Twig
$twig = Twig::create(__DIR__);
$app->add(TwigMiddleware::create($app, $twig));

// Add IP address middleware
$checkProxyHeaders = true;
$trustedProxies = ['10.0.0.1', '10.0.0.2'];
$app->add(new IpAddress($checkProxyHeaders, $trustedProxies));

$app->get('/', function (Request $request, Response $response) use ($twig) {
    /** @var Twig_Environment $twig */
    return $twig->render($response, 'index.html.twig',
        ['ip' => $request->getAttribute('ip_address')]);
});

return $app;
