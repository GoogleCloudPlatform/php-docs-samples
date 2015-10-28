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

use GoogleCloudPlatform\DocsSamples\Pubsub\DatastoreHelper;
use GoogleCloudPlatform\DocsSamples\Pubsub\PubsubHelper;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Ring\Client\StreamHandler;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Debug;

// composer autoloading
require_once __DIR__.'/../vendor/autoload.php';

$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__.'/../templates' ];
$app['config'] = json_decode(file_get_contents(__DIR__.'/../config.json'), true);
$app['debug'] = true;

Debug::enable();

// Authenticate your API Client
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Pubsub::PUBSUB);
$client->addScope(Google_Service_Datastore::DATASTORE);
$client->addScope(Google_Service_Datastore::USERINFO_EMAIL);

// this should be done automatically in the underlying Google_Client class
$client->setHttpClient(new HttpClient(['handler' => new StreamHandler()]));

$app['google.client'] = $client;

$app->get('/', function () use ($app) {
    $projectId = $app['config']['project'];
    $topic = $app['config']['topic'];
    $subscription = $app['config']['subscription'];
    $token = $app['config']['pubsub-token'];

    $projectPath = sprintf('projects/%s', $projectId);
    $topicName = sprintf('projects/%s/topics/%s', $projectId, $topic);
    $pubsub = new Google_Service_Pubsub($app['google.client']);

    $util = new PubsubHelper();
    $util->setupTopic($projectPath, $topicName, $pubsub);
    $util->setupSubscription(
        $projectId,
        $topicName,
        $subscription,
        $token,
        $pubsub
    );

    return $app['twig']->render('pubsub.html.twig', [
        'project' => $projectId,
        'topic' => $topic,
        'subscription' => $subscription,
        'endpoint' => $util->getEndpoint($projectId, $token),
    ]);
});

$app->get('/fetch_messages', function () use ($app) {
    $datasetId = $app['config']['project'];

    $projectName = sprintf('projects/%s', $app['config']['project']);
    $topicName = sprintf('%s/topics/%s', $projectName, $app['config']['topic']);
    $datastore = new Google_Service_Datastore($app['google.client']);

    $util = new DatastoreHelper();
    $query = $util->createQuery();
    $response = $datastore->datasets->runQuery($datasetId, $query);

    $messages = [];
    foreach ($response->getBatch()->getEntityResults() as $entityResult) {
        $properties = $entityResult->getEntity()->getProperties();
        $messages[] = $properties['message']->getStringValue();
    }

    return new JsonResponse($messages);
});

$app->post('/receive_message', function() use ($app) {
    if ($app['request']->query->get('token') != $app['config']['pubsub-token']) {
        return new Response('', 403);
    }

    // pull the message from the post body
    $json = $app['request']->getContent();
    $request = json_decode($json, true);
    if (!isset($request['message']['data']) || !$message = base64_decode($request['message']['data'])) {
        syslog(LOG_INFO, 'Invalid Request: '.$json);
        return new Response('', 400);
    }

    $datasetId = $app['config']['project'];
    $service = new Google_Service_Datastore($app['google.client']);
    $dataset = $service->datasets;

    $util = new DatastoreHelper();
    $idRequest = $util->createUniqueKeyRequest();
    $uniqueId = $dataset->allocateIds($datasetId, $idRequest);
    $key = $uniqueId->getKeys()[0];

    $request = $util->createMessageRequest($key, $message);
    $result = $dataset->commit($datasetId, $request);

    return new Response();
});

$app->post('/send_message', function() use ($app) {
    if ($messageText = $app['request']->get('message')) {
        $projectName = sprintf('projects/%s', $app['config']['project']);
        $topicName = sprintf('%s/topics/%s', $projectName, $app['config']['topic']);
        $pubsub = new Google_Service_Pubsub($app['google.client']);

        // create pubsub request
        $request = new Google_Service_Pubsub_PublishRequest();
        $message = new Google_Service_Pubsub_PubsubMessage();
        $message->setData(base64_encode($messageText));
        $request->setMessages([$message]);

        $pubsub->projects_topics->publish($topicName, $request);

        return new Response('', 204);
    }
});

$app->run();
