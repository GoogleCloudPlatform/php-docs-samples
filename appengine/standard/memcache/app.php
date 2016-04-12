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
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();

// Simple HTTP GET and PUT operators.
$app->get('/', function() {
    static $message = <<<EOT
<html><body>
<p>A simple REST server that stores and retrieves values from memcache.
<p>GET and PUT to<br>
<a href="/memcache">/memcache/{key}</a><br>
<a href="/memcached">/memcached/{key}</a>
</body></html>
EOT;
    return $message;
});

$app->get('/memcache/{key}', function ($key) {
    # [START memcache_get]
    $memcache = new Memcache;
    return $memcache->get($key);
    # [END memcache_get]
});

$app->put('/memcache/{key}', function ($key, Request $request) {
    # [START memcache_put]
    $memcache = new Memcache;
    $value = $request->getContent();
    return $memcache->set($key, $value);
    # [END memcache_put]
});

$app->get('/memcached/{key}', function ($key) {
    # [START memcached_get]
    $memcache = new Memcached;
    return $memcache->get($key);
    # [END memcached_get]
});

$app->put('/memcached/{key}', function ($key, Request $request) {
    # [START memcached_put]
    $memcache = new Memcached;
    $value = $request->getContent();
    return $memcache->set($key, $value);
    # [END memcached_put]
});

return $app;
