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
use Google\Cloud\Storage\StorageClient;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    /** @var Google\Cloud\StorageClient */
    $storage = $app['storage'];
    $bucketName = $app['bucket_name'];
    $objectName = $app['object_name'];
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $content = $object->exists() ? $object->downloadAsString() : '';
    $escapedContent = htmlspecialchars($content);
    $form = <<<EOF
    <h1>Storage Example</h1>
    <h3>Write [<a href="https://cloud.google.com/appengine/docs/flexible/php/using-cloud-storage">docs</a>]:</h3>
    <form action="/write" method="post">
        Some file content:<br />
        <textarea name="content"></textarea><br />
        <input type="submit" />
    </form>
EOF;
    if ($content) {
        $form .= "<p><strong>Your content:</strong><p><p>$escapedContent</p>";
    }
    return $form;
});

/**
 * Write to a Storage bucket.
 * @see https://cloud.google.com/appengine/docs/flexible/php/using-cloud-storage
 */
$app->post('/write', function (Request $request) use ($app) {
    /** @var Google\Cloud\StorageClient */
    $storage = $app['storage'];
    $bucketName = $app['bucket_name'];
    $objectName = $app['object_name'];
    $content = $request->get('content');
    $metadata = ['contentType' => 'text/plain'];
    $storage->bucket($bucketName)->upload($content, [
        'name' => $objectName,
        'metadata' => $metadata,
    ]);
    return $app->redirect('/');
});

$app['storage'] = function () use ($app) {
    $projectId = $app['project_id'];
    $storage = new StorageClient([
        'projectId' => $projectId
    ]);
    return $storage;
};
# [END gae_flex_storage_app]

return $app;
