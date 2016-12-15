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

# [START import_client]
use Google\Cloud\Storage\StorageClient;
# [END import_client]
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function () use ($app) {
    /** @var Google\Cloud\StorageClient */
    $storage = $app['storage'];
    $bucketName = $app['bucket_name'];

    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object('hello.txt');
    $content = $object->exists() ? $object->downloadAsString() : '';

    return $app['twig']->render('storage.html.twig', [
        'content' => $content
    ]);
});

/**
 * Write to a Storage bucket.
 * @see https://cloud.google.com/appengine/docs/flexible/php/using-cloud-storage
 */
$app->post('/write', function (Request $request) use ($app) {
    /** @var Google\Cloud\StorageClient */
    $storage = $app['storage'];
    $bucketName = $app['bucket_name'];
    $content = $request->get('content');
    # [START write]
    $metadata = ['contentType' => 'text/plain'];
    $storage->bucket($bucketName)->upload($content, [
        'name' => 'hello.txt',
        'metadata' => $metadata,
    ]);
    # [END write]
    return $app->redirect('/');
});

$app['storage'] = function () use ($app) {
    $projectId = $app['project_id'];
    # [START create_client]
    $storage = new StorageClient([
        'projectId' => $projectId
    ]);
    # [END create_client]
    return $storage;
};

return $app;
