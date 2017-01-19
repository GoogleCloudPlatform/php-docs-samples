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

require_once __DIR__ . '/vendor/autoload.php';

if (count($argv) < 3) {
    die('usage: create_keyring.php [project_id] [name]');
}

list($projectId, $name) = array_slice($argv, 1);

# [START create_key_ring]
// Instantiate the client
$client = new Google_Client();

// Authorize the client using Application Default Credentials
// @see https://developers.google.com/identity/protocols/application-default-credentials
$client->useApplicationDefaultCredentials();

// Set the required scopes to access the Key Management Service API$client->setScopes([
$client->setScopes([
    'https://www.googleapis.com/auth/cloud-platform'
]);

// Create key in the "global" location.
$location = 'global';

// The resource name of the location associated with the key rings.
$parent = sprintf('projects/%s/locations/%s',
    $projectId,
    $location
);

$keyRing = new Google_Service_CloudKMS_KeyRing();

// create the key ring for your project
$kms = new Google_Service_CloudKMS($client);
$kms->projects_locations_keyRings->create(
    $parent,
    $keyRing,
    ['keyRingId' => $name]
);
# [END create_key_ring]
