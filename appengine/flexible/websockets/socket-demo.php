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

# [START gae_flex_websockets_app]
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

// Install composer dependencies with "composer install"
// @see http://getcomposer.org for more information.
require __DIR__ . '/vendor/autoload.php';

// Forwards any incoming messages to all connected clients
class SocketDemo implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Connection opened!\n";
        echo "\t" . $this->clients->count() . " connection(s) active.\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $output = "Message received: " . $msg . "\n";
        echo $output;
        foreach ($this->clients as $client) {
            $client->send($output);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Connection closed gracefully!\n";
        echo "\t" . $this->clients->count() . " connection(s) active.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
        echo "Connection closed due to error: " . $e->getMessage() . "\n";
        echo "\t" . $this->clients->count() . " connection(s) active.\n";
    }
}
# [END gae_flex_websockets_app]

return new SocketDemo;
