<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Samples\AppEngine\Storage;

use Google\Auth\Credentials\GCECredentials;

require_once __DIR__ . '/vendor/autoload.php';

$bucketName = getenv('GOOGLE_STORAGE_BUCKET');
$projectId = getenv('GOOGLE_CLOUD_PROJECT');
$defaultBucketName = sprintf('%s.appspot.com', $projectId);

register_stream_wrapper($projectId);

if ($bucketName == '<your-bucket-name>') {
    return 'Set the GOOGLE_STORAGE_BUCKET environment variable to the name of '
        . 'your cloud storage bucket in <code>app.yaml</code>';
}

if (!in_array('gs', stream_get_wrappers())) {
    return 'This application can only run in AppEngine or the Dev AppServer environment.';
}

if ($_SERVER['REQUEST_URI'] == '/write/public') {
    $contents = sprintf('new file written at %s', date('Y-m-d H:i:s'));
    $publicUrl = write_public($bucketName, 'public_file.txt', $contents);
    header('Location: ' . $publicUrl);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch ($_SERVER['REQUEST_URI']) {
        case '/write':
            write_file($bucketName, 'hello.txt', $_REQUEST['content']);
            break;
        case '/write/options':
            write_options($bucketName, 'hello_options.txt', $_REQUEST['content']);
            break;
        case '/write/stream':
            write_stream($bucketName, 'hello_stream.txt', $_REQUEST['content']);
            break;
        case '/write/caching':
            write_with_caching($bucketName, 'hello_caching.txt', $_REQUEST['content']);
            break;
        case '/write/metadata':
            write_metadata(
                $bucketName,
                'hello_metadata.txt',
                $_REQUEST['content'],
                ['foo' => 'bar', 'baz' => 'qux']
            );
            break;
        case '/write/default':
            if (!GCECredentials::onGce()) {
                exit('This sample will only work when running on App Engine');
            }
            write_default('hello_default.txt', $_REQUEST['content']);
            break;
        case '/write/default/stream':
            if (!GCECredentials::onGce()) {
                exit('This sample will only work when running on App Engine');
            }
            write_default_stream('hello_default_stream.txt', $_REQUEST['content']);
            break;
        case '/user/upload':
            upload_file($bucketName);
            exit;
    }
    header('Location: /');
    exit;
}

$params = [];
$objects = [
    'hello' => "gs://${bucketName}/hello.txt",
    'options' => "gs://${bucketName}/hello_options.txt",
    'stream' => "gs://${bucketName}/hello_stream.txt",
    'caching' => "gs://${bucketName}/hello_caching.txt",
    'metadata' => "gs://${bucketName}/hello_metadata.txt",
    'default' => "gs://${defaultBucketName}/hello_default.txt",
    'default_stream' => "gs://${defaultBucketName}/hello_default_stream.txt",
];
foreach ($objects as $name => $object) {
    $params[$name] = file_exists($object) ? file_get_contents($object) : '';
}

// load file metadata
$metadata = [];
if (file_exists($objects['metadata'])) {
    $metadata = read_metadata($projectId, $bucketName, 'hello_metadata.txt');
}

?>
<!DOCTYPE HTML>
<html>
  <head>
    <title>Storage Example</title>
  </head>

  <body>
    <h1>Storage Example</h1>

    <div>
        <h3>
            Write
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/#simple_file_write">docs</a>]:
        </h3>
        <form action="/write" method="post">
            Some file content:<br />
            <textarea name="content"></textarea><br />
            <input type="submit" />
        </form>

        <?php if ($params['hello']): ?>
            <p><strong>Your content:</strong><p>
            <p><?= $params['hello'] ?></p>
        <?php endif ?>
    </div>

    <div>
        <h3>
            Write with Options
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/#simple_file_write">docs</a>]:
        </h3>
        <form action="/write/options" method="post">
            Some file content:<br />
            <textarea name="content"></textarea><br />
            <input type="submit" />
        </form>

        <?php if ($params['options']): ?>
            <p><strong>Your content:</strong><p>
            <p><?= $params['options'] ?></p>
        <?php endif ?>
    <div>

    <div>
        <h3>
            Stream Write
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/#streamed_file_write">docs</a>]:
        </h3>
        <form action="/write/stream" method="post">
            Some file content:<br />
            <textarea name="content"></textarea><br />
            <input type="submit" />
        </form>

        <?php if ($params['stream']): ?>
            <p><strong>Your content:</strong><p>
            <p><?= $params['stream'] ?></p>
        <?php endif ?>
    <div>

    <div>
        <h3>
            Write with Caching
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/advanced#cached_file_reads">docs</a>]:
        </h3>
        <form action="/write/caching" method="post">
            Some file content:<br />
            <textarea name="content"></textarea><br />
            <input type="submit" />
        </form>

        <?php if ($params['caching']): ?>
            <p><strong>Your content:</strong><p>
            <p><?= $params['caching'] ?></p>
        <?php endif ?>
    <div>

    <div>
        <h3>
            Write with Metadata
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/advanced#reading_and_writing_custom_metadata">docs</a>]:
        </h3>
        <form action="/write/metadata" method="post">
            Some file content:<br />
            <textarea name="content"></textarea><br />
            <input type="submit" />
        </form>

        <?php if ($params['metadata']): ?>
            <p><strong>Your content:</strong><p>
            <p><?= $params['metadata'] ?></p>
            <p><strong>Your metadata:</strong><p>
            <pre>
<?php foreach ((array) $metadata as $key => $value): ?>
    <?= $key ?>: <?= $value ?>

<?php endforeach ?>
            </pre>
        <?php endif ?>
    <div>

    <div>
        <h3>
            Write (default)
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/setup">docs</a>]:
        </h3>
        <form action="/write/default" method="post">
            Some file content:<br />
            <textarea name="content"></textarea><br />
            <input type="submit" />
        </form>

        <?php if ($params['default']): ?>
            <p><strong>Your content:</strong><p>
            <p><?= $params['default'] ?></p>
        <?php endif ?>
    <div>

    <div>
        <h3>
            Stream Write (default)
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/setup">docs</a>]:
        </h3>
        <form action="/write/default/stream" method="post">
            Some file content:<br />
            <textarea name="content"></textarea><br />
            <input type="submit" />
        </form>

        <?php if ($params['default_stream']): ?>
            <p><strong>Your content:</strong><p>
            <p><?= $params['default_stream'] ?></p>
        <?php endif ?>
    <div>

    <div>
        <h3>
            Write and Serve Public Files
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/public_access#serving_files_directly_from_google_cloud_storage">docs</a>]:
        </h3>
        <p><em><a href="/write/public" target="_blank">Example of writing and serving a public file</a></em></p>
    </div>

    <div>
        <h3>
            User Uploads
            [<a href="https://cloud.google.com/appengine/docs/php/googlestorage/user_upload">docs</a>]:
        </h3>

        <?php # [START user_upload_form]?>
        <form action="/user/upload" enctype="multipart/form-data" method="post">
            Files to upload: <br>
           <input type="file" name="uploaded_files" size="40">
           <input type="submit" value="Send">
        </form>
        <?php # [END user_upload_form]?>
    </div>
</html>

