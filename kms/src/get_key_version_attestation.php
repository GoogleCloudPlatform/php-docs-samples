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

use Exception;
// [START kms_get_key_version_attestation]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

function get_key_version_attestation(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $versionId = '123'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the key name.
    $keyVersionName = $client->cryptokeyVersionName($projectId, $locationId, $keyRingId, $keyId, $versionId);

    // Call the API.
    $version = $client->getCryptoKeyVersion($keyVersionName);

    // Only HSM keys have an attestation. For other key types, the attestion
    // will be NULL.
    $attestation = $version->getAttestation();
    if (!$attestation) {
        throw new Exception('no attestation - attestations only exist on HSM keys');
    }

    printf('Got key attestation: %s' . PHP_EOL, $attestation->getContent());

    return $attestation;
}
// [END kms_get_key_version_attestation]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
