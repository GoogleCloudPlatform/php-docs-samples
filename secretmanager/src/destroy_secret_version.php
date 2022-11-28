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

/*
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/secretmanager/README.md
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\SecretManager;

// [START secretmanager_destroy_secret_version]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;

/**
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 * @param string $versionId Your version ID (e.g. 'latest' or '5');
 */
function destroy_secret_version(string $projectId, string $secretId, string $versionId): void
{
    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient();

    // Build the resource name of the secret version.
    $name = $client->secretVersionName($projectId, $secretId, $versionId);

    // Destroy the secret version.
    $response = $client->destroySecretVersion($name);

    // Print a success message.
    printf('Destroyed secret version: %s', $response->getName());
}
// [END secretmanager_destroy_secret_version]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
