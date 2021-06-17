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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if ($argc < 6 || $argc > 8) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID NAMESPACE_ID SERVICE_ID ENDPOINT_ID [IP] [PORT]\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $namespaceId, $serviceId, $endpointId) = $argv;
$ip = isset($argv[6]) ? $argv[6] : '';
$port = isset($argv[7]) ? (int) $argv[7] : 0;

// [START servicedirectory_create_endpoint]
use Google\Cloud\ServiceDirectory\V1beta1\RegistrationServiceClient;
use Google\Cloud\ServiceDirectory\V1beta1\Endpoint;

/** Uncomment and populate these variables in your code */
// $projectId = '[YOUR_PROJECT_ID]';
// $locationId = '[YOUR_GCP_REGION]';
// $namespaceId = '[YOUR_NAMESPACE_NAME]';
// $serviceId = '[YOUR_SERVICE_NAME]';
// $endpointId = '[YOUR_ENDPOINT_NAME]';
// $ip = '[IP_ADDRESS]';  // (Optional) Defaults to ''
// $port = [PORT];  // (Optional) Defaults to 0

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
// [END servicedirectory_create_endpoint]
