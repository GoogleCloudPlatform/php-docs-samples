<?php
/**
 * Copyright 2024 Google Inc.
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

// [START storage_control_quickstart_sample]
// Includes the autoloader for libraries installed with composer
require __DIR__ . '/../vendor/autoload.php';

// Imports the Google Cloud client library
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\GetStorageLayoutRequest;

// Instantiates a client
$storageControlClient = new StorageControlClient();

// The name for the new bucket
$bucketName = 'my-new-bucket';

// Set project to "_" to signify global bucket
$formattedName = $storageControlClient->storageLayoutName('_', $bucketName);
$request = (new GetStorageLayoutRequest())->setName($formattedName);

$response = $storageControlClient->getStorageLayout($request);

echo 'Performed get_storage_layout request for ' . $response->getName() . PHP_EOL;
// [END storage_control_quickstart_sample]
return $response;
