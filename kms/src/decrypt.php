<?php
/**
 * Copyright 2018 Google Inc.
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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/kms/README.md
 */


// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 7) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID KEYRING_ID CRYPTOKEY_ID CIPHERTEXT_FILENAME PLAINTEXT_FILENAME\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $keyRingId, $cryptoKeyId, $ciphertextFileName, $plaintextFileName) = $argv;

# [START kms_decrypt]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\CryptoKey;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $locationId = 'The location ID of the crypto key. Can be "global", "us-west1", etc.';
// $keyRingId = 'The KMS key ring ID';
// $cryptoKeyId = 'The KMS key ID';
// $ciphertextFileName = 'The path to the file containing ciphertext to decrypt';
// $plaintextFileName = 'The path to write the plaintext';

$kms = new KeyManagementServiceClient();

// The resource name of the CryptoKey.
$cryptoKey = new CryptoKey();
$cryptoKeyName = $kms->cryptoKeyName($projectId, $locationId, $keyRingId, $cryptoKeyId);

$ciphertext = file_get_contents($ciphertextFileName);
$response = $kms->decrypt($cryptoKeyName, $ciphertext);

// Write the encrypted text to a file.
file_put_contents($plaintextFileName, $response->getPlaintext());
printf('Saved decrypted text to %s' . PHP_EOL, $plaintextFileName);
# [END kms_decrypt]
