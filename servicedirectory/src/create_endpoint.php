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

// [START servicedirectory_create_endpoint]
use Google\Cloud\ServiceDirectory\V1beta1\RegistrationServiceClient;
use Google\Cloud\ServiceDirectory\V1beta1\Endpoint;

/**
 * @param string $projectId     Your Cloud project ID
 * @param string $locationId    Your GCP region
 * @param string $namespaceId   Your namespace name
 * @param string $serviceId     Your service name
 * @param string $endpointId    Your endpoint name
 * @param string $ip            (Optional) Defaults to ''
 * @param int    $port          (Optional) Defaults to 0
 */
function create_endpoint(
    string $projectId,
    string $locationId,
    string $namespaceId,
    string $serviceId,
    string $endpointId,
    string $ip = '',
    int $port = 0
): void {
    // Instantiate a client.
    $client = new RegistrationServiceClient();

    // Construct Endpoint object.
    $endpointObject = (new Endpoint())
        ->setAddress($ip)
        ->setPort($port);

    // Run request.
    $serviceName = RegistrationServiceClient::serviceName($projectId, $locationId, $namespaceId, $serviceId);
    $endpoint = $client->createEndpoint($serviceName, $endpointId, $endpointObject);

    // Print results.
    printf('Created Endpoint: %s' . PHP_EOL, $endpoint->getName());
    printf('  IP: %s' . PHP_EOL, $endpoint->getAddress());
    printf('  Port: %d' . PHP_EOL, $endpoint->getPort());
}
// [END servicedirectory_create_endpoint]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
