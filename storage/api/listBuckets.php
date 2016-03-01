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
if (empty($argv[1])) {
    die("usage: php listBuckets [project_id]\n");
}

$projectId = $argv[1];

// Authenticate your API Client
$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->addScope(Google_Service_Storage::DEVSTORAGE_FULL_CONTROL);

$storage = new Google_Service_Storage($client);

/**
 * Google Cloud Storage API request to retrieve the list of buckets in your project.
 */
$buckets = $storage->buckets->listBuckets($projectId);

foreach ($buckets['items'] as $bucket) {
    printf("%s\n", $bucket->getName());
}
// [END all]
