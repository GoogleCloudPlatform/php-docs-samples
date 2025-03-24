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

// [START parametermanager_create_param_version_with_secret]
// Import necessary classes for creating a parameter version.
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\CreateParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\ParameterVersion;
use Google\Cloud\ParameterManager\V1\ParameterVersionPayload;

/**
 * Creates a parameter version with an secret reference.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $parameterId The Parameter ID (e.g. 'my-param')
 * @param string $versionId The Version ID (e.g. 'my-param-version')
 * @param string $secretId The ID of the secret to be referenced (e.g. 'projects/my-project/secrets/my-secret/versions/latest')
 */
function create_param_version_with_secret(string $projectId, string $parameterId, string $versionId, string $secretId): void
{
    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient();

    // Build the resource name of the parent object.
    $parent = $client->parameterName($projectId, 'global', $parameterId);

    // Build payload.
    $payload = sprintf('{"username": "test-user", "password": "__REF__(//secretmanager.googleapis.com/%s)"}', $secretId);

    // Create a new ParameterVersionPayload object and set the payload with secret reference.
    $parameterVersionPayload = new ParameterVersionPayload();
    $parameterVersionPayload->setData($payload);

    // Create a new ParameterVersion object and set the payload.
    $parameterVersion = new ParameterVersion();
    $parameterVersion->setPayload($parameterVersionPayload);

    // Prepare the request with the parent and parameter version object.
    $request = (new CreateParameterVersionRequest())
        ->setParent($parent)
        ->setParameterVersionId($versionId)
        ->setParameterVersion($parameterVersion);

    // Call the API to create the parameter version.
    $newParameterVersion = $client->createParameterVersion($request);
    printf('Created parameter version: %s' . PHP_EOL, $newParameterVersion->getName());
}
// [END parametermanager_create_param_version_with_secret]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
