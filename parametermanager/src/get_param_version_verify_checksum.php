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

// [START parametermanager_get_param_version_verify_checksum]
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\GetParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\ParameterVersion\ChecksumSource;
use Google\Cloud\ParameterManager\V1\View;

/**
 * Gets a parameter version and verifies its payload checksum.
 *
 * @param string $projectId The Google Cloud Project ID (e.g. 'my-project')
 * @param string $parameterId The Parameter ID (e.g. 'my-param')
 * @param string $versionId The Version ID (e.g. 'my-param-version')
 */
function get_param_version_verify_checksum(string $projectId, string $parameterId, string $versionId): void
{
    // Create a client for the Parameter Manager service.
    $client = new ParameterManagerClient();

    // Build the resource name of the parameter version.
    $parameterVersionName = $client->parameterVersionName($projectId, 'global', $parameterId, $versionId);

    // Build the request with the full view to get the payload and checksum.
    $request = (new GetParameterVersionRequest())
        ->setName($parameterVersionName)
        ->setView(View::FULL);

    // Get the parameter version.
    $parameterVersion = $client->getParameterVersion($request);

    // Verify the payload checksum.
    $payload = $parameterVersion->getPayload();
    if (hexdec(hash('crc32c', $payload->getData())) !== $payload->getDataCrc32C()) {
        throw new \UnexpectedValueException(sprintf('Checksum mismatch for %s: data corrupted', $parameterVersion->getName()));
    }

    // Print the verification result and the checksum source.
    printf('Verified checksum for parameter version: %s' . PHP_EOL, $parameterVersion->getName());
    printf('Checksum source: %s' . PHP_EOL, ChecksumSource::name($parameterVersion->getChecksumSource()));
}
// [END parametermanager_get_param_version_verify_checksum]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
