<?php
/*
 * Copyright 2025 Google LLC.
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

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 5) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID PARAMETER_ID VERSION_ID\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $parameterId, $versionId) = $argv;

// [START parametermanager_regional_quickstart]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\CreateParameterRequest;
use Google\Cloud\ParameterManager\V1\CreateParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\GetParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\ParameterVersionPayload;
use Google\Cloud\ParameterManager\V1\ParameterVersion;
use Google\Cloud\ParameterManager\V1\ParameterFormat;
use Google\Cloud\ParameterManager\V1\Parameter;

/** Uncomment and populate these variables in your code */
// $projectId = 'YOUR_GOOGLE_CLOUD_PROJECT' (e.g. 'my-project');
// $locationId = 'YOUR_LOCATION_ID' (e.g. 'us-central1');
// $parameterId = 'YOUR_PARAMETER_ID' (e.g. 'my-param');
// $versionId = 'YOUR_VERSION_ID' (e.g. 'my-version');

// Specify regional endpoint.
$options = ['apiEndpoint' => "parametermanager.$locationId.rep.googleapis.com"];

// Create a client for the Parameter Manager service.
$client = new ParameterManagerClient($options);

// Build the resource name of the parent object.
$parent = $client->locationName($projectId, $locationId);

// Create a new Parameter object and set the format.
$parameter = (new Parameter())
    ->setFormat(ParameterFormat::JSON);

// Prepare the request with the parent, parameter ID, and the parameter object.
$request = (new CreateParameterRequest())
    ->setParent($parent)
    ->setParameterId($parameterId)
    ->setParameter($parameter);

// Crete the parameter.
$newParameter = $client->createParameter($request);

// Print the new parameter name
printf('Created regional parameter %s with format %s' . PHP_EOL, $newParameter->getName(), ParameterFormat::name($newParameter->getFormat()));

// Create a new ParameterVersionPayload object and set the json data.
$payload = '{"username": "test-user", "host": "localhost"}';
$parameterVersionPayload = new ParameterVersionPayload();
$parameterVersionPayload->setData($payload);

// Create a new ParameterVersion object and set the payload.
$parameterVersion = new ParameterVersion();
$parameterVersion->setPayload($parameterVersionPayload);

// Prepare the request with the parent and parameter version object.
$request = (new CreateParameterVersionRequest())
    ->setParent($newParameter->getName())
    ->setParameterVersionId($versionId)
    ->setParameterVersion($parameterVersion);

// Create the parameter version.
$newParameterVersion = $client->createParameterVersion($request);

// Print the new parameter version name
printf('Created regional parameter version: %s' . PHP_EOL, $newParameterVersion->getName());

// Prepare the request with the parent for retrieve parameter version.
$request = (new GetParameterVersionRequest())
    ->setName($newParameterVersion->getName());

// Get the parameter version.
$parameterVersion = $client->getParameterVersion($request);

// Print the parameter version name
printf('Payload: %s' . PHP_EOL, $parameterVersion->getPayload()->getData());
// [END parametermanager_regional_quickstart]
