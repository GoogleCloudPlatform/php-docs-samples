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

use Google\Cloud\Logger\AppEngineFlexHandler;
use Google\Cloud\Logging\LoggingClient;
use Silex\Application;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();
$app['project_id'] = getenv('GCLOUD_PROJECT');
// register twig
$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__
]);

$app->get('/', function () use ($app) {
    if (empty($app['project_id'])) {
        return 'Set the GCLOUD_PROJECT environment variable to run locally';
    }
    $projectId = $app['project_id'];
    # [START list_entries]
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);
    $logger = $logging->logger('logging-sample');
    $logs = $logger->entries([
        'pageSize' => 10,
        'orderBy' => 'timestamp desc'
    ]);
    # [END list_entries]
    return $app['twig']->render('index.html.twig', ['logs' => $logs]);
});

$app->post('/log', function (Request $request) use ($app) {
    $projectId = $app['project_id'];
    $text = $request->get('text');
    # [START write_log]
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);
    $logger = $logging->psrLogger('logging-sample');
    $logger->notice($text);
    # [END write_log]
    return $app->redirect('/');
});

// add AppEngineFlexHandler on prod
$app->register(new MonologServiceProvider());
if (isset($_SERVER['GAE_VM']) && $_SERVER['GAE_VM'] === 'true') {
    $app['monolog.handler'] = new AppEngineFlexHandler();
}

return $app;
