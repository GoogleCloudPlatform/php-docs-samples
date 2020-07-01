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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 3) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID\n", basename(__FILE__));
}
list($_, $projectId, $locationId) = $argv;

// [START servicedirectory_quickstart]
use Google\Cloud\ServiceDirectory\V1beta1\RegistrationServiceClient;

/** Uncomment and populate these variables in your code */
// $projectId = '[YOUR_PROJECT_ID]';
// $locationId = '[YOUR_GCP_REGION]';

// Instantiate a client.
$client = new RegistrationServiceClient();

// Run request.
$pagedResponse = $client->listNamespaces(RegistrationServiceClient::locationName($projectId, $locationId));

// Iterate over each namespace and print its name.
print('Namespaces: ' . PHP_EOL);
foreach ($pagedResponse->iterateAllElements() as $namespace_pb) {
    printf('%s' . PHP_EOL, $namespace_pb->getName());
}
// [END servicedirectory_quickstart]
