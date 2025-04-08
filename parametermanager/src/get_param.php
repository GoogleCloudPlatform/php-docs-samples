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

// [START parametermanager_get_param]
// Import necessary classes for retrieve a parameter version.
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\GetParameterRequest;
use Google\Cloud\ParameterManager\V1\ParameterFormat;

/**
 * Retrieves a parameter using the Parameter Manager SDK for GCP.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $parameterId The Parameter ID (e.g. 'my-param')
 */
function get_param(string $projectId, string $parameterId): void
{
    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient();

    // Build the resource name of the parameter.
    $parameterName = $client->parameterName($projectId, 'global', $parameterId);

    // Prepare the request to get the parameter.
    $request = (new GetParameterRequest())
        ->setName($parameterName);

    // Retrieve the parameter using the client.
    $parameter = $client->getParameter($request);

    // Print the retrieved parameter details.
    printf('Found parameter %s with format %s' . PHP_EOL, $parameter->getName(), ParameterFormat::name($parameter->getFormat()));
}
// [END parametermanager_get_param]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
