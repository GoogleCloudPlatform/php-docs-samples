<?php

/**
 * Copyright 2020 Google Inc.
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

namespace Google\Cloud\Samples\ServiceDirectory;

// [START servicedirectory_delete_service]
use Google\Cloud\ServiceDirectory\V1\Client\RegistrationServiceClient;
use Google\Cloud\ServiceDirectory\V1\DeleteServiceRequest;

/**
 * @param string $projectId     Your Cloud project ID
 * @param string $locationId    Your GCP region
 * @param string $namespaceId   Your namespace name
 * @param string $serviceId     Your service name
 */
function delete_service(
    string $projectId,
    string $locationId,
    string $namespaceId,
    string $serviceId
): void {
    // Instantiate a client.
    $client = new RegistrationServiceClient();

    // Run request.
    $serviceName = RegistrationServiceClient::serviceName($projectId, $locationId, $namespaceId, $serviceId);
    $deleteServiceRequest = (new DeleteServiceRequest())
        ->setName($serviceName);
    $client->deleteService($deleteServiceRequest);

    // Print results.
    printf('Deleted Service: %s' . PHP_EOL, $serviceName);
}
// [END servicedirectory_delete_service]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
