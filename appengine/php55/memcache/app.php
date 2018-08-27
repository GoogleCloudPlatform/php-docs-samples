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

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function (Application $app, Request $request) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    $memcache = new Memcached;
    return $twig->render('memcache.html.twig', [
        'who' => $memcache->get('who'),
        'count' => $memcache->get('count'),
        'host' => $request->getHost(),
    ]);
});

$app->post('/', function (Application $app, Request $request) {
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];
    # [START gae_memcache_who_count]
    $memcache = new Memcached;
    $memcache->set('who', $request->get('who'));
    return $twig->render('memcache.html.twig', [
        'who' => $request->get('who'),
        'count' => $memcache->increment('count', 1, 0),
        'host' => $request->getHost(),
    ]);
    # [END gae_memcache_who_count]
});

// Simple HTTP GET and PUT operators.
$app->get('/memcache/{key}', function ($key) {
    # [START gae_memcache_get]
    $memcache = new Memcache;
    return $memcache->get($key);
    # [END gae_memcache_get]
});

$app->put('/memcache/{key}', function ($key, Request $request) {
    # [START gae_memcache_put]
    $memcache = new Memcache;
    $value = $request->getContent();
    return $memcache->set($key, $value);
    # [END gae_memcache_put]
});

$app->get('/memcached/{key}', function ($key) {
    $memcache = new Memcached;
    return $memcache->get($key);
});

$app->put('/memcached/{key}', function ($key, Request $request) {
    $memcache = new Memcached;
    $value = $request->getContent();
    return $memcache->set($key, $value);
});

return $app;
