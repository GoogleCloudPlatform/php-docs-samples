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

// [START kms_quickstart]
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\ListKeyRingsRequest;

function quickstart(
    string $projectId = 'my-project',
    string $locationId = 'us-east1'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the parent location name.
    $locationName = $client->locationName($projectId, $locationId);

    // Call the API.
    $listKeyRingsRequest = (new ListKeyRingsRequest())
        ->setParent($locationName);
    $keyRings = $client->listKeyRings($listKeyRingsRequest);

    // Example of iterating over key rings.
    printf('Key rings in %s:' . PHP_EOL, $locationName);
    foreach ($keyRings as $keyRing) {
        printf('%s' . PHP_EOL, $keyRing->getName());
    }

    return $keyRings;
}
// [END kms_quickstart]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
