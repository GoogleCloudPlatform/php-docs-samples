<?php

/*
 * Copyright 2017 Google Inc.
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

// [START log_payload]
require_once __DIR__ . '/vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Google\Cloud\Logging\LoggingClient;

$app = new Silex\Application();

$app->get('/', function () use ($app) {
    return 'Hello World';
});

$app->post('/log_payload', function (Request $request) use ($app) {
    $content = $request->getContent();
    $logging = new LoggingClient();
    $logName = 'my-log';
    $logger = $logging->logger($logName);
    $logging_text = sprintf('Received task with payload: %s', $content);
    $entry = $logger->entry($logging_text);
    $logger->write($entry);
    return sprintf('Received task with payload: %s', $content);
});

$app['debug'] = true;

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $app;
}

$app->run();
// [END log_payload]