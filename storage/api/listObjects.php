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
if (empty($argv[2])) {
    die("usage: php listBuckets [project_id] [bucket_name]\n");
}

$projectId = $argv[1];
$bucketName = $argv[2];

// Authenticate your API Client
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Storage::DEVSTORAGE_FULL_CONTROL);

$storage = new Google_Service_Storage($client);

try {
    // Google Cloud Storage API request to retrieve the list of objects in your project.
    $objects = $storage->objects->listObjects($bucketName);
} catch (Google_Service_Exception $e) {
    // The bucket doesn't exist!
    if ($e->getCode() == 404) {
        exit(sprintf("Invalid bucket name \"%s\"\n", $bucketName));
    }
    throw $e;
}

foreach ($objects['items'] as $object) {
    printf("%s\n", $object->getName());
}
// [END all]
