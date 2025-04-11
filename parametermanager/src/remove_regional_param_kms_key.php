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

/*
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/parametermanager/README.md
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\ParameterManager;

// [START parametermanager_remove_regional_param_kms_key]
// Import necessary classes for updating a parameter.
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\GetParameterRequest;
use Google\Cloud\ParameterManager\V1\UpdateParameterRequest;
use Google\Protobuf\FieldMask;

/**
 * Update a regional parameter by removing kms key using the Parameter Manager SDK for GCP.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId The Parameter Location (e.g. 'us-central1')
 * @param string $parameterId The Parameter ID (e.g. 'my-param')
 */
function remove_regional_param_kms_key(string $projectId, string $locationId, string $parameterId): void
{
    // Specify regional endpoint.
    $options = ['apiEndpoint' => "parametermanager.$locationId.rep.googleapis.com"];

    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient($options);

    // Build the resource name of the parameter.
    $parameterName = $client->parameterName($projectId, $locationId, $parameterId);

    // Prepare the request to get the parameter.
    $request = (new GetParameterRequest())
        ->setName($parameterName);

    // Retrieve the parameter using the client.
    $parameter = $client->getParameter($request);

    $parameter->clearKmsKey();

    $updateMask = (new FieldMask())
        ->setPaths(['kms_key']);

    // Prepare the request to update the parameter.
    $request = (new UpdateParameterRequest())
        ->setUpdateMask($updateMask)
        ->setParameter($parameter);

    // Update the parameter using the client.
    $updatedParameter = $client->updateParameter($request);

    // Print the parameter details.
    printf('Removed kms key for regional parameter %s' . PHP_EOL, $updatedParameter->getName());
}
// [END parametermanager_remove_regional_param_kms_key]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
