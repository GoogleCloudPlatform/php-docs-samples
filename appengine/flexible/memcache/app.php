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

use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

// Create the container
AppFactory::setContainer($container = new Container());
$container->set('view', function () {
    return Twig::create(__DIR__);
});

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

$container->set('memcached', function () {
    # [START gae_flex_redislabs_memcache]
    $endpoint = getenv('MEMCACHE_ENDPOINT');
    $username = getenv('MEMCACHE_USERNAME');
    $password = getenv('MEMCACHE_PASSWORD');
    $memcached = new Memcached;
    if ($username && $password) {
        $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        $memcached->setSaslAuthData($username, $password);
    }
    list($host, $port) = explode(':', $endpoint);
    if (!$memcached->addServer($host, $port)) {
        throw new Exception("Failed to add server $host:$port");
    }
    # [END gae_flex_redislabs_memcache]
    return $memcached;
});

$app->get('/vars', function (Request $request, Response $response) {
    $vars = [
        'MEMCACHE_PORT_11211_TCP_ADDR',
        'MEMCACHE_PORT_11211_TCP_PORT'
    ];
    $lines = array();
    foreach ($vars as $var) {
        $val = getenv($var);
        array_push($lines, "$var = $val");
    }
    $response->getBody()->write(implode("\n", $lines));
    return $response->withHeader('Content-Type', 'text/plain');
});

$app->get('/', function (Request $request, Response $response) use ($container) {
    $memcached = $container->get('memcached');
    return $container->get('view')->render($response, 'memcache.html.twig', [
        'who' => $memcached->get('who'),
        'count' => $memcached->get('count'),
        'host' => $request->getUri()->getHost(),
    ]);
});

$app->post('/reset', function (Request $request, Response $response) use ($container) {
    $memcached = $container->get('memcached');
    $memcached->delete('who');
    $memcached->set('count', 0);
    return $container->get('view')->render($response, 'memcache.html.twig', [
        'host' => $request->getUri()->getHost(),
        'count' => 0,
        'who' => '',
    ]);
});

$app->post('/', function (Request $request, Response $response) use ($container) {
    parse_str((string) $request->getBody(), $postData);
    $who = $postData['who'] ?? '';
    $memcached = $container->get('memcached');
    $memcached->set('who', $who);
    $count = $memcached->increment('count');
    if (false === $count) {
        // Potential race condition.  Use binary protocol to avoid.
        $memcached->set('count', 0);
        $count = 0;
    }
    return $container->get('view')->render($response, 'memcache.html.twig', [
        'who' => $who,
        'count' => $count,
        'host' => $request->getUri()->getHost(),
    ]);
});

$app->get('/memcached/{key}', function (Request $request, Response $response, $args) use ($container) {
    $memcached = $container->get('memcached');
    $value = $memcached->get($args['key']);
    $response->getBody()->write((string) $value);
    return $response;
});

$app->put('/memcached/{key}', function (Request $request, Response $response, $args) use ($container) {
    $memcached = $container->get('memcached');
    $value = (string) $request->getBody();
    $success = $memcached->set($args['key'], $value, time() + 600); // 10 minutes expiration
    $response->getBody()->write((string) $success);
    return $response;
});

return $app;
