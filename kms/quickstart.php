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

// Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

// Lists keys in the "global" location.
$location = 'global';

// The resource name of the location associated with the KeyRings
$parent = sprintf('projects/%s/locations/%s', $projectId, $location);

// Instantiate the client
$client = new Google_Client();

// Authorize the client using Application Default Credentials
// @see https://developers.google.com/identity/protocols/application-default-credentials
$client->useApplicationDefaultCredentials();

// Set the required scopes to access the Key Management Service API
$client->setScopes(array(
    'https://www.googleapis.com/auth/cloud-platform'
));

// Instantiate the Key Management Service API
$kms = new Google_Service_CloudKMS($client);

// list all key rings for your project
$keyRings = $kms->projects_locations_keyRings->listProjectsLocationsKeyRings(
    $projectId,
    array('parent' => $parent)
);

// Print the key rings
echo 'Key Rings: ' . PHP_EOL;
foreach ($keyRings as $keyRing) {
    echo $keyRing->name . PHP_EOL;
}
# [END kms_quickstart]
return $keyRings;
