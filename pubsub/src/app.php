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

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Google\Cloud\Samples\Pubsub\DatastoreHelper;

// composer autoloading
require_once __DIR__.'/../vendor/autoload.php';

$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__.'/../templates' ];
$app['project_id'] = getenv('GOOGLE_PROJECT_NAME');
$app['topic'] = getenv('TOPIC_NAME') ?: 'php-pubsub-example';

// Authenticate your API Client
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Pubsub::PUBSUB);
$client->addScope(Google_Service_Datastore::DATASTORE);
$client->addScope(Google_Service_Datastore::USERINFO_EMAIL);

$app['google_client'] = $client;

$app->get('/', function () use ($app) {
    return $app['twig']->render('pubsub.html.twig', [
        'project_id' => $app['project_id'],
    ]);
});

$app->get('/fetch_messages', function () use ($app) {
    /** @var Google_Client $client */
    $client = $app['google_client'];
    $projectId = $app['project_id'];

    // Retrieve all messages from datastore
    // For a more complete demo of datastore,
    // @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/datastore
    $datastore = new DatastoreHelper($client, $projectId);
    $messages = $datastore->fetchMessages();

    return new JsonResponse($messages);
});

$app->post('/receive_message', function() use ($app) {
    /** @var Google_Client $client */
    $client = $app['google_client'];
    $projectId = $app['project_id'];

    // pull the message from the post body
    $json = $app['request']->getContent();
    $request = json_decode($json, true);
    if (!isset($request['message']['data']) || !$message = base64_decode($request['message']['data'])) {
        return new Response('', 400);
    }

    // Store the received message in datastore
    // For a more complete demo of datastore,
    // @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/datastore
    $datastore = new DatastoreHelper($client, $projectId);
    $datastore->storeMessage($message);

    return new Response();
});

$app->post('/send_message', function() use ($app) {
    // send the pubsub message
    if ($messageText = $app['request']->get('message')) {
        /** @var Google_Client $client */
        $client = $app['google_client'];
        $projectName = sprintf('projects/%s', $app['project_id']);
        $topicName = sprintf('%s/topics/%s', $projectName, $app['topic']);

        $pubsub = new Google_Service_Pubsub($client);

        // create pubsub message object
        $message = new Google_Service_Pubsub_PubsubMessage();
        $message->setData(base64_encode($messageText));
        // create pubsub request
        $request = new Google_Service_Pubsub_PublishRequest();
        $request->setMessages([$message]);

        $pubsub->projects_topics->publish($topicName, $request);

        return new Response('', 204);
    }

    return new Response('', 400);
});

return $app;
