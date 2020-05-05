<?php
/*
 * Copyright 2020 Google LLC.
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

declare(strict_types=1);

// [START kms_create_key_ring]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\KeyRing;

function create_key_ring_sample(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $id = 'my-key-ring'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the parent location name.
    $locationName = $client->locationName($projectId, $locationId);

    // Build the key ring.
    $keyRing = new KeyRing();

    // Call the API.
    $createdKeyRing = $client->createKeyRing($locationName, $id, $keyRing);
    printf('Created key ring: %s' . PHP_EOL, $createdKeyRing->getName());
    return $createdKeyRing;
}
// [END kms_create_key_ring]

if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID ID\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $id) = $argv;
    create_key_ring_sample($projectId, $locationId, $id);
}
