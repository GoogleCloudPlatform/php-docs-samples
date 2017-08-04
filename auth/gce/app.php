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

# [START auth_cloud_explicit_compute_engine]
use Google\Auth\Credentials\GCECredentials;
use Google\Cloud\Storage\StorageClient;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();

$app->get('/', function () use ($app) {
    # Explicitly use service account credentials by using the default Compute
    # Engine service account.
    $projectId = $app['project_id'];
    $gce = new GCECredentials();
    $config = [
        'projectId' => $projectId,
        'credentialsFetcher' => $gce,
    ];
    $storage = new StorageClient($config);

    # Make an authenticated API request (listing storage buckets)
    $buckets = $storage->buckets();
    # [END auth_cloud_explicit_compute_engine]

    $content = '';
    foreach ($buckets as $bucket) {
        $content .= $bucket->name();
        $content .= ', ';
    }
    $content = substr($content, 0, -2);
    $escapedContent = htmlspecialchars($content);
    $html = "<h1>Storage Buckets</h1>";
    if ($content) {
        $html .= "<p><strong>Your Cloud Storage buckets:</strong><p><p>$escapedContent</p>";
    }
    return $html;
});

return $app;
