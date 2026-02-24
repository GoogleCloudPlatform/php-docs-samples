<?php
/*
 * Copyright 2026 Google LLC.
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

// [START kms_get_retired_resource]
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\GetRetiredResourceRequest;

function get_retired_resource(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $retiredResourceId = 'my-retired-resource'
): void {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the resource name of the retired resource.
    $name = $client->retiredResourceName($projectId, $locationId, $retiredResourceId);

    // Call the API.
    $request = (new GetRetiredResourceRequest())
        ->setName($name);
    $response = $client->getRetiredResource($request);

    printf('Retired Resource Name: %s' . PHP_EOL, $response->getName());
    printf('Original Resource: %s' . PHP_EOL, $response->getOriginalResource());
}
// [END kms_get_retired_resource]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
