<?php

require_once __DIR__ . '/vendor/autoload.php';

if (count($argv) < 3) {
    die('usage: create_key.php [project_id] [name]');
}

list($projectId, $name) = array_slice($argv, 1);

# [START create_key_ring]
// Instantiate the client
$client = new Google_Client();

// Authorize the client using Application Default Credentials
// @see https://developers.google.com/identity/protocols/application-default-credentials
$client->useApplicationDefaultCredentials();

// Set the required scopes to access the Key Management Service API$client->setScopes([
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
