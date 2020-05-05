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

// [START kms_create_key_version]
use Google\Cloud\Kms\V1\CryptoKeyVersion;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

function create_key_version_sample(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the parent key name.
    $keyName = $client->cryptoKeyName($projectId, $locationId, $keyRingId, $keyId);

    // Build the key version.
    $version = new CryptoKeyVersion();

    // Call the API.
    $createdVersion = $client->createCryptoKeyVersion($keyName, $version);
    printf('Created key version: %s' . PHP_EOL, $createdVersion->getName());
    return $createdVersion;
}
// [END kms_create_key_version]

if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID KEY_RING_ID KEY_ID\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $keyRingId, $keyId) = $argv;
    create_key_version_sample($projectId, $locationId, $keyRingId, $keyId);
}
