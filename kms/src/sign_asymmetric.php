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

// [START kms_sign_asymmetric]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\Digest;

function sign_asymmetric_sample(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $versionId = '123',
    string $message = '...'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the key version name.
    $keyVersionName = $client->cryptoKeyVersionName($projectId, $locationId, $keyRingId, $keyId, $versionId);

    // Calculate the hash.
    $hash = hash('sha256', $message, true);

    // Build the digest.
    //
    // Note: Key algorithms will require a varying hash function. For
    // example, EC_SIGN_P384_SHA384 requires SHA-384.
    $digest = (new Digest())
        ->setSha256($hash);

    // Call the API.
    $signResponse = $client->asymmetricSign($keyVersionName, $digest);
    printf('Signature: %s' . PHP_EOL, $signResponse->getSignature());
    return $signResponse;
}
// [END kms_sign_asymmetric]

if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID KEY_RING_ID KEY_ID VERSION_ID MESSAGE\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $keyRingId, $keyId, $versionId, $message) = $argv;
    sign_asymmetric_sample($projectId, $locationId, $keyRingId, $keyId, $versionId, $message);
}
