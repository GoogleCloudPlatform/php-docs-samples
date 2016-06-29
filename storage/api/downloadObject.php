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

// [START all]
// composer autoloading
require_once __DIR__ . '/vendor/autoload.php';

// grab the first argument
if (empty($argv[3])) {
    die("usage: php downloadObject [project_id] [bucket_name] [object_name]"
        . " [optional_download_name]\n");
}

$projectId = $argv[1];
$bucketName = $argv[2];
$objectName = $argv[3];
$downloadName = isset($argv[4]) ? $argv[4] : $argv[3];

// Authenticate your API Client
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Storage::DEVSTORAGE_FULL_CONTROL);

$storage = new Google_Service_Storage($client);

try {
    // Google Cloud Storage API request to retrieve the list of objects in your
    // project.
    $object = $storage->objects->get($bucketName, $objectName);
} catch (Google_Service_Exception $e) {
    // The bucket doesn't exist!
    if ($e->getCode() == 404) {
        exit(sprintf("Invalid bucket or object names (\"%s\", \"%s\")\n",
            $bucketName, $objectName));
    }
    throw $e;
}

// build the download URL
$uri = sprintf('https://storage.googleapis.com/%s/%s?alt=media&generation=%s',
    $bucketName, $objectName, $object->generation);
$http = $client->authorize();
$response = $http->get($uri);

if ($response->getStatusCode() != 200) {
    exit('download failed!' . $response->getBody());
}
if (file_exists($downloadName)) {
    exit(sprintf("File \"%s\" exists! Cowardly refusing to overwrite it\n",
        $downloadName));
}

// write the file
file_put_contents($downloadName, $response->getBody());
echo sprintf("File written to %s\n", $downloadName);

// [END all]
