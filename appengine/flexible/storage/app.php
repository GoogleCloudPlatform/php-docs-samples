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

# [START gae_flex_storage_app]
use DI\Container;
use Google\Cloud\Storage\StorageClient;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

// Create App
AppFactory::setContainer($container = new Container());
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

$container = $app->getContainer();

$app->get('/', function (Request $request, Response $response) use ($container) {
    /** @var Google\Cloud\StorageClient */
    $storage = $container->get('storage');
    $bucketName = $container->get('bucket_name');
    $objectName = $container->get('object_name');
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $content = $object->exists() ? $object->downloadAsString() : '';
    $escapedContent = htmlspecialchars($content);
    $response->getBody()->write(<<<EOF
    <h1>Storage Example</h1>
    <h3>Write [<a href="https://cloud.google.com/appengine/docs/flexible/php/using-cloud-storage">docs</a>]:</h3>
    <form action="/write" method="post">
        Some file content:<br />
        <textarea name="content"></textarea><br />
        <input type="submit" />
    </form>
EOF
    );
    if ($content) {
        $response->getBody()->write(
            "<p><strong>Your content:</strong><p><p>$escapedContent</p>"
        );
    }
    return $response;
});

/**
 * Write to a Storage bucket.
 * @see https://cloud.google.com/appengine/docs/flexible/php/using-cloud-storage
 */
$app->post('/write', function (Request $request, Response $response) use ($container) {
    /** @var Google\Cloud\StorageClient */
    $storage = $container->get('storage');
    $bucketName = $container->get('bucket_name');
    $objectName = $container->get('object_name');
    parse_str((string) $request->getBody(), $postData);
    $metadata = ['contentType' => 'text/plain'];
    $storage->bucket($bucketName)->upload($postData['content'] ?? '', [
        'name' => $objectName,
        'metadata' => $metadata,
    ]);
    return $response
        ->withStatus(302)
        ->withHeader('Location', '/');
});

$container->set('storage', function () use ($container) {
    $projectId = $container->get('project_id');
    $storage = new StorageClient([
        'projectId' => $projectId
    ]);
    return $storage;
});
# [END gae_flex_storage_app]

return $app;
