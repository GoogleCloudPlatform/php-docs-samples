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

// [START parametermanager_bind_tags_to_regional_param]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\CreateParameterRequest;
use Google\Cloud\ParameterManager\V1\Parameter;
use Google\Cloud\ResourceManager\V3\Client\TagBindingsClient;
use Google\Cloud\ResourceManager\V3\CreateTagBindingRequest;
use Google\Cloud\ResourceManager\V3\TagBinding;

/**
 * Creates a parameter and binds a tag value to it.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId The Parameter Location (e.g. 'us-central1')
 * @param string $parameterId The Parameter ID (e.g. 'my-param')
 * @param string $tagValue The tag value (e.g. 'tagValues/1234567890')
 */
function bind_tags_to_regional_param(string $projectId, string $locationId, string $parameterId, string $tagValue): void
{
    // Specify regional endpoint.
    $options = ['apiEndpoint' => "parametermanager.$locationId.rep.googleapis.com"];

    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient($options);

    // Create the Resource Manager tag bindings client.
    $tagOptions = ['apiEndpoint' => "$locationId-cloudresourcemanager.googleapis.com"];
    $tagBindingsClient = new TagBindingsClient($tagOptions);

    // Build the resource name of the parent object.
    $parent = $client->locationName($projectId, $locationId);

    // Build the request.
    $request = (new CreateParameterRequest())
        ->setParent($parent)
        ->setParameterId($parameterId)
        ->setParameter(new Parameter());

    // Create the parameter.
    $newParameter = $client->createParameter($request);

    // Print the new parameter name.
    printf('Created regional parameter: %s' . PHP_EOL, $newParameter->getName());

    // Build the tag binding.
    $tagBinding = (new TagBinding())
        ->setParent('//parametermanager.googleapis.com/' . $newParameter->getName())
        ->setTagValue($tagValue);

    // Build the request.
    $request = (new CreateTagBindingRequest())
        ->setTagBinding($tagBinding);

    // Create the tag binding and wait for the operation to complete.
    $operationResponse = $tagBindingsClient->createTagBinding($request);
    $operationResponse->pollUntilComplete();

    // Check if the operation succeeded.
    if ($operationResponse->operationSucceeded()) {
        printf('Created tag binding: %s' . PHP_EOL, $operationResponse->getResult()->getName());
    } else {
        printf('Error in creating tag binding: %s' . PHP_EOL, $operationResponse->getError()->getMessage());
    }
}
// [END parametermanager_bind_tags_to_regional_param]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
