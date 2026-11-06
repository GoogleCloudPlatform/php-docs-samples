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

// [START parametermanager_get_regional_param_tags]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\GetParameterRequest;
use Google\Cloud\ResourceManager\V3\Client\TagBindingsClient;
use Google\Cloud\ResourceManager\V3\ListTagBindingsRequest;

/**
 * Gets the tag bindings of a parameter.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId The Parameter Location (e.g. 'us-central1')
 * @param string $parameterId The Parameter ID (e.g. 'my-param')
 */
function get_regional_param_tags(string $projectId, string $locationId, string $parameterId): void
{
    // Specify regional endpoint.
    $options = ['apiEndpoint' => "parametermanager.$locationId.rep.googleapis.com"];

    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient($options);

    // Create the Resource Manager tag bindings client.
    $tagOptions = ['apiEndpoint' => "$locationId-cloudresourcemanager.googleapis.com"];
    $tagBindingsClient = new TagBindingsClient($tagOptions);

    // Build the resource name of the parameter.
    $parameterName = $client->parameterName($projectId, $locationId, $parameterId);

    // Get the parameter.
    $parameter = $client->getParameter((new GetParameterRequest())->setName($parameterName));

    // Build the request.
    $request = (new ListTagBindingsRequest())
        ->setParent('//parametermanager.googleapis.com/' . $parameter->getName());

    // List the tag bindings of the parameter.
    foreach ($tagBindingsClient->listTagBindings($request) as $binding) {
        printf('Found regional parameter tag: %s' . PHP_EOL, $binding->getTagValue());
    }
}
// [END parametermanager_get_regional_param_tags]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
