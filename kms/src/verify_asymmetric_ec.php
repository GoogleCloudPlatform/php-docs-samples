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

// [START kms_verify_asymmetric_signature_ec]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

function verify_asymmetric_ec_sample(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $versionId = '123',
    string $message = '...',
    string $signature = '...'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the key version name.
    $keyVersionName = $client->cryptoKeyVersionName($projectId, $locationId, $keyRingId, $keyId, $versionId);

    // Get the public key.
    $publicKey = $client->getPublicKey($keyVersionName);

    // Verify the signature. The hash algorithm must correspond to the key
    // algorithm. The openssl_verify command returns 1 on success, 0 on falure.
    $verified = openssl_verify($message, $signature, $publicKey->getPem(), OPENSSL_ALGO_SHA256) === 1;
    printf('Signature verified: %s', $verified);
    return $verified;
}
// [END kms_verify_asymmetric_signature_ec]

if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID KEY_RING_ID KEY_ID VERSION_ID MESSAGE SIGNATURE\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $keyRingId, $keyId, $versionId, $message, $signature) = $argv;
    verify_asymmetric_ec_sample($projectId, $locationId, $keyRingId, $keyId, $versionId, $message, $signature);
}
