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

// [START kms_update_key_add_rotation_schedule]
use Google\Cloud\Kms\V1\CryptoKey;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Protobuf\Duration;
use Google\Protobuf\FieldMask;
use Google\Protobuf\Timestamp;

function update_key_add_rotation(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the key name.
    $keyName = $client->cryptoKeyName($projectId, $locationId, $keyRingId, $keyId);

    // Build the key.
    $key = (new CryptoKey())
        ->setName($keyName)

        // Rotate the key every 30 days.
        ->setRotationPeriod((new Duration())
            ->setSeconds(60 * 60 * 24 * 30)
        )

        // Start the first rotation in 24 hours.
        ->setNextRotationTime((new Timestamp())
            ->setSeconds(time() + 60 * 60 * 24)
        );

    // Create the field mask.
    $updateMask = (new FieldMask())
        ->setPaths(['rotation_period', 'next_rotation_time']);

    // Call the API.
    $updatedKey = $client->updateCryptoKey($key, $updateMask);
    printf('Updated key: %s' . PHP_EOL, $updatedKey->getName());

    return $updatedKey;
}
// [END kms_update_key_add_rotation_schedule]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
