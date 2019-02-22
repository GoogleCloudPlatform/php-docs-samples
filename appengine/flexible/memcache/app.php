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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];
$app['memcached'] = function () {
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
};

$app->get('/vars', function () {
    $vars = array('MEMCACHE_PORT_11211_TCP_ADDR',
        'MEMCACHE_PORT_11211_TCP_PORT');
    $lines = array();
    foreach ($vars as $var) {
        $val = getenv($var);
        array_push($lines, "$var = $val");
    }
    return new Response(
        implode("\n", $lines),
        200,
        ['Content-Type' => 'text/plain']);
});

$app->get('/', function (Application $app, Request $request) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    /** @var Memcached $memcached */
    $memcached = $app['memcached'];
    return $twig->render('memcache.html.twig', [
        'who' => $memcached->get('who'),
        'count' => $memcached->get('count'),
        'host' => $request->getHttpHost(),
    ]);
});

$app->post('/reset', function (Application $app, Request $request) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    /** @var Memcached $memcached */
    $memcached = $app['memcached'];
    $memcached->delete('who');
    $memcached->set('count', 0);
    return $twig->render('memcache.html.twig', [
        'host' => $request->getHttpHost(),
        'count' => 0,
        'who' => '',
    ]);
});

$app->post('/', function (Application $app, Request $request) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    /** @var Memcached $memcached */
    $memcached = $app['memcached'];
    $memcached->set('who', $request->get('who'));
    $count = $memcached->increment('count');
    if (false === $count) {
        // Potential race condition.  Use binary protocol to avoid.
        $memcached->set('count', 0);
        $count = 0;
    }
    return $twig->render('memcache.html.twig', [
        'who' => $request->get('who'),
        'count' => $count,
        'host' => $request->getHttpHost(),
    ]);
});

$app->get('/memcached/{key}', function (Application $app, $key) {
    /** @var Memcached $memcached */
    $memcached = $app['memcached'];
    return $memcached->get($key);
});

$app->put('/memcached/{key}', function (Application $app, $key, Request $request) {
    /** @var Memcached $memcached */
    $memcached = $app['memcached'];
    $value = $request->getContent();
    return $memcached->set($key, $value, time() + 600); // 10 minutes expiration
});

return $app;
