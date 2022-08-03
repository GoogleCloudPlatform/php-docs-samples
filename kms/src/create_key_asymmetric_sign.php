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

namespace Google\Cloud\Samples\Kms;

// [START kms_create_key_asymmetric_sign]
use Google\Cloud\Kms\V1\CryptoKey;
use Google\Cloud\Kms\V1\CryptoKey\CryptoKeyPurpose;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionAlgorithm;
use Google\Cloud\Kms\V1\CryptoKeyVersionTemplate;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Protobuf\Duration;

function create_key_asymmetric_sign(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $id = 'my-asymmetric-signing-key'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the parent key ring name.
    $keyRingName = $client->keyRingName($projectId, $locationId, $keyRingId);

    // Build the key.
    $key = (new CryptoKey())
        ->setPurpose(CryptoKeyPurpose::ASYMMETRIC_SIGN)
        ->setVersionTemplate((new CryptoKeyVersionTemplate())
            ->setAlgorithm(CryptoKeyVersionAlgorithm::RSA_SIGN_PKCS1_2048_SHA256)
        )

        // Optional: customize how long key versions should be kept before destroying.
        ->setDestroyScheduledDuration((new Duration())
            ->setSeconds(24 * 60 * 60)
        );

    // Call the API.
    $createdKey = $client->createCryptoKey($keyRingName, $id, $key);
    printf('Created asymmetric signing key: %s' . PHP_EOL, $createdKey->getName());

    return $createdKey;
}
// [END kms_create_key_asymmetric_sign]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
