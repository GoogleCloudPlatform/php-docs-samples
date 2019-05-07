<?php
/**
 * Copyright 2019 Google LLC.
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

// [START gae_flex_websockets_server]
// Install composer dependencies with "composer install"
// @see http://getcomposer.org for more information.
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/socket-demo.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$socketDemo = new SocketDemo;

$port = 8000;

$server = IoServer::factory(
    new HttpServer(
        new WsServer($socketDemo)
    ),
    $port
);
// [END gae_flex_websockets_server]

if (PHP_SAPI == 'cli') {
    $server->run();
}

return $server;
