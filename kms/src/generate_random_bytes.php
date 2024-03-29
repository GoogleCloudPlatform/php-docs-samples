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

namespace Google\Cloud\Samples\Kms;

// [START kms_generate_random_bytes]
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\GenerateRandomBytesRequest;
use Google\Cloud\Kms\V1\ProtectionLevel;

/**
 * Generate a random byte string using Cloud KMS.
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $locationId The location ID (e.g. us-east1).
 * @param int $numBytes The number of bytes to generate.
 */
function generate_random_bytes(
    string $projectId,
    string $locationId = 'us-east1',
    int $numBytes = 256
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the parent name.
    $locationName = $client->locationName($projectId, $locationId);

    // Call the API.
    $generateRandomBytesRequest = (new GenerateRandomBytesRequest())
        ->setLocation($locationName)
        ->setLengthBytes($numBytes)
        ->setProtectionLevel(ProtectionLevel::HSM);
    $randomBytesResponse = $client->generateRandomBytes($generateRandomBytesRequest);

    // The data comes back as raw bytes, which may include non-printable
    // characters. This base64-encodes the result so it can be printed below.
    $encodedData = base64_encode($randomBytesResponse->getData());
    printf('Random bytes: %s' . PHP_EOL, $encodedData);

    return $randomBytesResponse;
}
// [END kms_generate_random_bytes]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
