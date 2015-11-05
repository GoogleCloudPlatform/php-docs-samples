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

use Google\Cloud\Samples\Datastore\DatastoreHelper;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// create the silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app['twig.path'] = [ __DIR__.'/../templates' ];

// create the google api client
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Datastore::DATASTORE);
$client->addScope(Google_Service_Datastore::USERINFO_EMAIL);

// add the api client and project id to our silex app
$app['google_client'] = $client;
$app['project_id'] = getenv('GCP_PROJECT_ID');

$app->get('/', function () use ($app) {
    /** @var Google_Client $client */
    $client = $app['google_client'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    // run a simple query to retrieve the comments
    // - last 20 items ordered by created DESC
    $projectId = $app['project_id'];
    $datastore = new Google_Service_Datastore($client);
    $util = new DatastoreHelper();
    $query = $util->createSimpleQuery();
    $response = $datastore->datasets->runQuery($projectId, $query);

    // create an array of the queried DataStore comments
    // and pass them to the view layer
    $comments = [];
    foreach ($response->getBatch()->getEntityResults() as $entityResult) {
        $properties = $entityResult->getEntity()->getProperties();
        $comments[] = [
            'name' => $properties['name']->getStringValue(),
            'body' => $properties['body']->getStringValue(),
            'created' => $properties['created']->getDateTimeValue(),
        ];
    }

    return $twig->render('datastore.html.twig', [
        'project' => $projectId,
        'comments' => $comments,
    ]);
})->bind('home');

$app->post('/store', function(Request $request) use ($app) {
    /** @var Google_Client $client */
    $client = $app['google_client'];
    /** @var Symfony\Component\Routing\Generator\UrlGenerator $urlgen */
    $urlgen = $app['url_generator'];

    // pull the comment from the post body
    $name = $request->get('name');
    $body = $request->get('body');

    if (empty($name) || empty($body)) {
        $error = 'Invalid Request: "name" and "body" are required';
        return new Response($error, 400);
    }

    $util = new DatastoreHelper();

    // use our project ID for our dataset ID
    $datasetId = $app['project_id'];

    // create a datastore service object to call the APIs
    $datastore = new Google_Service_Datastore($client);

    // generate a unique key to store this item using the APIs
    $keyRequest = $util->createUniqueKeyRequest($datasetId);
    $uniqueId = $datastore->datasets->allocateIds($datasetId, $keyRequest);
    $key = $uniqueId->getKeys()[0];

    // submit the changes to datastore
    $request = $util->createCommentRequest($key, $name, $body);
    $result = $datastore->datasets->commit($datasetId, $request);

    return new Response('', 301, ['Location' => $urlgen->generate('home')]);
})->bind('store');

return $app;
