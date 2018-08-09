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

use Google\Cloud\Logging\LoggingClient;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();
$app['project_id'] = getenv('GOOGLE_PROJECT_ID');
// register twig
$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__
]);

$app->get('/', function () use ($app) {
    if (empty($app['project_id'])) {
        return 'Set the GOOGLE_PROJECT_ID environment variable to run locally';
    }
    $projectId = $app['project_id'];
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);
    $logger = $logging->logger('logging-sample');
    $logs = $logger->entries([
        'pageSize' => 10,
        'orderBy' => 'timestamp desc'
    ]);
    return $app['twig']->render('index.html.twig', ['logs' => $logs]);
});

$app->post('/log', function (Request $request) use ($app) {
    $projectId = $app['project_id'];
    $text = $request->get('text');
    # [START gae_flex_configure_logging]
    # [START creating_psr3_logger]
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);
    $logger = $logging->psrLogger('app');
    # [END creating_psr3_logger]
    $logger->notice($text);
    # [END gae_flex_configure_logging]
    return $app->redirect('/');
});

$app->get('/async_log', function (Request $request) use ($app) {
    $token = $request->query->get('token');
    $projectId = $app['project_id'];
    $text = $request->get('text');
    # [START enabling_batch]
    $logger = LoggingClient::psrBatchLogger('app');
    # [END enabling_batch]
    # [START using_the_logger]
    $logger->info('Hello World');
    $logger->error('Oh no');
    # [END using_the_logger]
    $logger->info("Token: $token");
    return 'Sent some logs';
});

return $app;
