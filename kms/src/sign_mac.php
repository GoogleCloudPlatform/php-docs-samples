<?php
/*
 * Copyright 2021 Google LLC.
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

// [START kms_sign_mac]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

/**
 * @return \Google\Cloud\Kms\V1\MacSignResponse
 */
function sign_mac_sample(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $versionId = '123',
    string $data = '...'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the key version name.
    $keyVersionName = $client->cryptoKeyVersionName($projectId, $locationId, $keyRingId, $keyId, $versionId);

    // Call the API.
    $signMacResponse = $client->macSign($keyVersionName, $data);

    // The data comes back as raw bytes, which may include non-printable
    // characters. This base64-encodes the result so it can be printed below.
    $signature = base64_encode($signMacResponse->getMac());
    printf('Signature: %s' . PHP_EOL, $signature);

    return $signMacResponse;
}
// [END kms_sign_mac]

if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID KEY_RING_ID KEY_ID VERSION_ID DATA\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $keyRingId, $keyId, $versionId, $data) = $argv;
    sign_mac_sample($projectId, $locationId, $keyRingId, $keyId, $versionId, $data);
}
