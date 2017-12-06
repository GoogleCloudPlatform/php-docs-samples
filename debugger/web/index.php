<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
# [START list_info_types]
use Google\Cloud\Debugger\Agent;

$agent = new Agent(['sourceRoot' => realpath('../')]);
# [END list_info_types]
$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->get('/', function() {
    return 'Silex version ' . Silex\Application::VERSION;
});

$app->get('/hello/{name}', function ($name) use ($app) {
    return $app['twig']->render('hello.html.twig', [
        'name' => $name
    ]);
});

$app->run();
