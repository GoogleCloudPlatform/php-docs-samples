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

if ($argc != 5) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID NAMESPACE_ID SERVICE_ID\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $namespaceId, $serviceId) = $argv;

// [START servicedirectory_resolve_service]
use Google\Cloud\ServiceDirectory\V1beta1\LookupServiceClient;
use Google\Cloud\ServiceDirectory\V1beta1\Service;

/** Uncomment and populate these variables in your code */
// $projectId = '[YOUR_PROJECT_ID]';
// $locationId = '[YOUR_GCP_REGION]';
// $namespaceId = '[YOUR_NAMESPACE_NAME]';
// $serviceId = '[YOUR_SERVICE_NAME]';

// Instantiate a client.
$client = new LookupServiceClient();

// Run request.
$serviceName = LookupServiceClient::serviceName($projectId, $locationId, $namespaceId, $serviceId);
$service = $client->resolveService($serviceName)->getService();

// Print results.
printf('Resolved Service: %s' . PHP_EOL, $service->getName());
print('Endpoints:' . PHP_EOL);
foreach ($service->getEndpoints() as $endpoint) {
    printf('  Name: %s' . PHP_EOL, $endpoint->getName());
    printf('    IP: %s' . PHP_EOL, $endpoint->getAddress());
    printf('    Port: %d' . PHP_EOL, $endpoint->getPort());
}
// [END servicedirectory_resolve_service]
