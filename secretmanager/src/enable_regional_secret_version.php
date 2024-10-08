<?php
/*
 * Copyright 2024 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/secretmanager/README.md
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\SecretManager;

// [START secretmanager_enable_regional_secret_version]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\EnableSecretVersionRequest;

/**
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Your secret Location (e.g. "us-central1")
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 * @param string $versionId Your version ID (e.g. 'latest' or '5');
 */
function enable_regional_secret_version(string $projectId, string $locationId, string $secretId, string $versionId): void
{
    # Specify regional endpoint.
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];

    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient($options);

    // Build the resource name of the secret version.
    $name = $client->projectLocationSecretSecretVersionName($projectId, $locationId, $secretId, $versionId);

    // Build the request.
    $request = EnableSecretVersionRequest::build($name);

    // Enable the secret version.
    $response = $client->enableSecretVersion($request);

    // Print a success message.
    printf('Enabled secret version: %s', $response->getName());
}
// [END secretmanager_enable_regional_secret_version]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
