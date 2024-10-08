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

// [START secretmanager_list_regional_secrets]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\ListSecretsRequest;

/**
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Your secret Location (e.g. "us-central1")
 */
function list_regional_secrets(string $projectId, string $locationId): void
{
    # Specify regional endpoint.
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];

    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient($options);

    // Build the resource name of the parent secret.
    $parent = $client->locationName($projectId, $locationId);

    // Build the request.
    $request = ListSecretsRequest::build($parent);

    // List all secrets.
    foreach ($client->listSecrets($request) as $secret) {
        printf('Found secret %s', $secret->getName());
    }
}
// [END secretmanager_list_regional_secrets]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
