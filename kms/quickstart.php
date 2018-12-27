<?php
/**
 * Copyright 2017 Google Inc.
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

# [START kms_quickstart]
// Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

// Import the Google Cloud KMS client library.
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

// Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

// Lists keys in the "global" location. Could also be "us-west1", etc.
$locationId = 'global';

// Instantiate the client
$kms = new KeyManagementServiceClient();

$locationName = $kms->locationName($projectId, $locationId);

// list all key rings for your project
$keyRings = $kms->listKeyRings($locationName);

// Print the key rings
echo 'Key Rings: ' . PHP_EOL;
foreach ($keyRings as $keyRing) {
    echo $keyRing->getName() . PHP_EOL;
}
# [END kms_quickstart]
return $keyRings;
