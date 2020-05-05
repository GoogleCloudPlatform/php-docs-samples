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

// [START kms_decrypt_symmetric]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

function decrypt_symmetric_sample(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $ciphertext = '...'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the key name.
    $keyName = $client->cryptoKeyName($projectId, $locationId, $keyRingId, $keyId);

    // Call the API.
    $decryptResponse = $client->decrypt($keyName, $ciphertext);
    printf('Plaintext: %s' . PHP_EOL, $decryptResponse->getPlaintext());
    return $decryptResponse;
}
// [END kms_decrypt_symmetric]

if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID KEY_RING_ID KEY_ID CIPHERTEXT\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $keyRingId, $keyId, $ciphertext) = $argv;
    decrypt_symmetric_sample($projectId, $locationId, $keyRingId, $keyId, $ciphertext);
}
