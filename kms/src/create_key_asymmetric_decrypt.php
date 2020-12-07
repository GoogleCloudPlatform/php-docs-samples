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

// [START kms_create_key_asymmetric_decrypt]
use Google\Cloud\Kms\V1\CryptoKey;
use Google\Cloud\Kms\V1\CryptoKey\CryptoKeyPurpose;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionAlgorithm;
use Google\Cloud\Kms\V1\CryptoKeyVersionTemplate;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

function create_key_asymmetric_decrypt_sample(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $id = 'my-asymmetric-decrypt-key'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the parent key ring name.
    $keyRingName = $client->keyRingName($projectId, $locationId, $keyRingId);

    // Build the key.
    $key = (new CryptoKey())
        ->setPurpose(CryptoKeyPurpose::ASYMMETRIC_DECRYPT)
        ->setVersionTemplate((new CryptoKeyVersionTemplate())
            ->setAlgorithm(CryptoKeyVersionAlgorithm::RSA_DECRYPT_OAEP_2048_SHA256)
        );

    // Call the API.
    $createdKey = $client->createCryptoKey($keyRingName, $id, $key);
    printf('Created asymmetric decryption key: %s' . PHP_EOL, $createdKey->getName());
    return $createdKey;
}
// [END kms_create_key_asymmetric_decrypt]


if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID KEY_RING_ID ID\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $keyRingId, $id) = $argv;
    create_key_asymmetric_decrypt_sample($projectId, $locationId, $keyRingId, $id);
}
