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

// [START kms_enable_key_version]
use Google\Cloud\Kms\V1\CryptoKeyVersion;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionState;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Protobuf\FieldMask;

function enable_key_version(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $versionId = '123'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the key version name.
    $keyVersionName = $client->cryptoKeyVersionName($projectId, $locationId, $keyRingId, $keyId, $versionId);

    // Create the updated version.
    $keyVersion = (new CryptoKeyVersion())
        ->setName($keyVersionName)
        ->setState(CryptoKeyVersionState::ENABLED);

    // Create the field mask.
    $updateMask = (new FieldMask())
        ->setPaths(['state']);

    // Call the API.
    $enabledVersion = $client->updateCryptoKeyVersion($keyVersion, $updateMask);
    printf('Enabled key version: %s' . PHP_EOL, $enabledVersion->getName());

    return $enabledVersion;
}
// [END kms_enable_key_version]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
