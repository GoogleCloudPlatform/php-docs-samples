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

namespace Google\Cloud\Samples\PubSub;

use DI\Container;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Datastore\DatastoreClient;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;

// Create Container
AppFactory::setContainer($container = new Container());
$container->set('view', function () {
    return Twig::create(__DIR__);
});

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) use ($container) {
    return $container->get('view')->render($response, 'pubsub.html.twig', [
        'project_id' => $container->get('project_id'),
    ]);
});

$app->get('/fetch_messages', function (Request $request, Response $response, $args) use ($container) {
    // get PUSH pubsub messages
    $projectId = $container->get('project_id');
    $subscriptionName = $container->get('subscription');
    $datastore = $container->get('datastore');
    $query = $datastore->query()->kind('PubSubPushMessage');
    $messages = [];
    $pushKeys = [];
    foreach ($datastore->runQuery($query) as $pushMessage) {
        $pushKeys[] = $pushMessage->key();
        $messages[] = $pushMessage['message'];
    }
    // delete PUSH messages
    if ($pushKeys) {
        $datastore->deleteBatch($pushKeys);
    }
    # [START gae_flex_pubsub_index]
    // get PULL pubsub messages
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $subscription = $pubsub->subscription($subscriptionName);
    $pullMessages = [];
    foreach ($subscription->pull(['returnImmediately' => true]) as $pullMessage) {
        $pullMessages[] = $pullMessage;
        $messages[] = $pullMessage->data();
    }
    // acknowledge PULL messages
    if ($pullMessages) {
        $subscription->acknowledgeBatch($pullMessages);
    }
    # [END gae_flex_pubsub_index]
    $response->getBody()->write(json_encode($messages));
    return $response;
});

$app->post('/receive_message', function (Request $request, Response $response, $args) use ($container) {
    // pull the message from the post body
    $json = json_decode($request->getContent(), true);
    if (
        !isset($json['message']['data'])
        || !$message = base64_decode($json['message']['data'])
    ) {
        return new Response('', 400);
    }
    // store the push message in datastore
    $datastore = $container->get('datastore');
    $message = $datastore->entity('PubSubPushMessage', [
        'message' => $message
    ]);
    $datastore->insert($message);
    return $response;
});

$app->post('/send_message', function (Request $request, Response $response, $args) use ($container) {
    $projectId = $container->get('project_id');
    $topicName = $container->get('topic');
    # [START gae_flex_pubsub_push]
    if ($message = (string) $request->getBody()) {
        // Publish the pubsub message to the topic
        $pubsub = new PubSubClient([
            'projectId' => $projectId,
        ]);
        $topic = $pubsub->topic($topicName);
        $topic->publish(['data' => $message]);
        return $response->withStatus(204);
    }
    # [END gae_flex_pubsub_push]
    return $response->withStatus(400);
});

$container->set('datastore', function () use ($container) {
    return new DatastoreClient([
        'projectId' => $container->get('project_id'),
    ]);
});

return $app;
