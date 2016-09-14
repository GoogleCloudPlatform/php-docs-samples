<?php

/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Google\Cloud\Samples\PubSub;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Google\Cloud\ServiceBuilder;
use Memcached;

$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function () use ($app) {
    return $app['twig']->render('pubsub.html.twig', [
        'project_id' => $app['project_id'],
    ]);
});

$app->get('/fetch_messages', function () use ($app) {
    $messages = $app['get_pull_messages'](true);
    $builder = new ServiceBuilder([
        'projectId' => $app['project_id'],
    ]);
    $pubsub = $builder->pubsub();
    $subscription = $pubsub->subscription($app['subscription']);
    $ackIds = [];
    foreach ($subscription->pull(['returnImmediately' => true]) as $message) {
        $ackIds[] = $message['ackId'];
        $messageData = $message['message']['data'];
        $messages[] = base64_decode($messageData);
    }
    if ($ackIds) {
        $subscription->acknowledgeBatch($ackIds);
    }
    return new JsonResponse($messages);
});

$app->post('/receive_message', function () use ($app) {
    // pull the message from the post body
    $json = $app['request']->getContent();
    $request = json_decode($json, true);
    if (
        !isset($request['message']['data'])
        || !$message = base64_decode($request['message']['data'])
    ) {
        return new Response('', 400);
    }
    // store the push message in memcache
    $app['save_pull_message']($message);
    return new Response();
});

$app->post('/send_message', function () use ($app) {
    // send the pubsub message
    if ($message = $app['request']->get('message')) {
        $builder = new ServiceBuilder([
            'projectId' => $app['project_id'],
        ]);
        $pubsub = $builder->pubsub();
        $topic = $pubsub->topic($app['topic']);
        $response = $topic->publish(['data' => $message]);
        return new Response('', 204);
    }
    return new Response('', 400);
});

$app['get_pull_messages'] = $app->protect(function ($clearMessages = false) {
    $memcache = new Memcached;
    if ($pullMessages = $memcache->get('pull-messages')) {
        if ($clearMessages) {
            $memcache->set('pull-messages', []);
        }
        return $pullMessages;
    }
    return [];
});

$app['save_pull_message'] = $app->protect(function ($message) use ($app) {
    $memcache = new Memcached;
    $messages = $app['get_pull_messages']();
    $messages[] = $message;
    $memcache->set('pull-messages', $messages);
});

return $app;
