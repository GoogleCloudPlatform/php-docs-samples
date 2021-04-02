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

# [START creating_psr3_logger_import]
use Google\Cloud\Logging\LoggingClient;
# [END creating_psr3_logger_import]
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

// Create Twig
$twig = Twig::create(__DIR__);
$app->add(TwigMiddleware::create($app, $twig));

$projectId = getenv('GCLOUD_PROJECT');

$app->get('/', function (Request $request, Response $response) use ($projectId, $twig) {
    if (empty($projectId)) {
        $response->getBody()->write('Set the GCLOUD_PROJECT environment variable to run locally');
        return $response;
    }
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);
    $logger = $logging->logger('app');
    $oneDayAgo = (new \DateTime('-1 day'))->format('c'); // ISO-8061
    $logs = $logger->entries([
        'pageSize' => 10,
        'resultLimit' => 10,
        'orderBy' => 'timestamp desc',
        'filter' => sprintf('timestamp >= "%s"', $oneDayAgo),
    ]);
    return $twig->render($response, 'index.html.twig', ['logs' => $logs]);
});

$app->post('/log', function (Request $request, Response $response) use ($projectId) {
    parse_str((string) $request->getBody(), $postData);
    # [START gae_flex_configure_logging]
    # [START creating_psr3_logger]
    $logging = new LoggingClient([
        'projectId' => $projectId
    ]);
    $logger = $logging->psrLogger('app');
    # [END creating_psr3_logger]
    $logger->notice($postData['text'] ?? '');
    # [END gae_flex_configure_logging]
    return $response
        ->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/async_log', function (Request $request, Response $response) use ($projectId) {
    $token = $request->getUri()->getQuery('token');
    # [START enabling_batch]
    $logger = LoggingClient::psrBatchLogger('app');
    # [END enabling_batch]
    # [START using_the_logger]
    $logger->info('Hello World');
    $logger->error('Oh no');
    # [END using_the_logger]
    $logger->info("Token: $token");
    $response->getBody()->write('Sent some logs');
    return $response;
});

return $app;
