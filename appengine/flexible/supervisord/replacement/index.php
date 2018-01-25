<?php

require_once __DIR__ . '/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

$port = getenv('PORT') ? intval(getenv('PORT')) : 8080;

$loop = Factory::create();
$server = new Server(function (ServerRequestInterface $request) {
    return new Response(
        200,
        array(
            'Content-Type' => 'text/plain'
        ),
        "Hello World!\n"
    );
});
$socket = new \React\Socket\Server('0.0.0.0:' . $port, $loop);
$server->listen($socket);
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$loop->run();
