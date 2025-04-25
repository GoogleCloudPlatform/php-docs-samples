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

// [START secretmanager_regional_iam_grant_access]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;

// Import the Secret Manager IAM library.
use Google\Cloud\Iam\V1\Binding;
use Google\Cloud\Iam\V1\GetIamPolicyRequest;
use Google\Cloud\Iam\V1\SetIamPolicyRequest;

/**
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Your secret Location (e.g. "us-central1")
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 * @param string $member Your member (e.g. 'user:foo@example.com')
 */
function regional_iam_grant_access(string $projectId, string $locationId, string $secretId, string $member): void
{
    // Specify regional endpoint.
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];

    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient($options);

    // Build the resource name of the secret.
    $name = $client->projectLocationSecretName($projectId, $locationId, $secretId);

    // Get the current IAM policy.
    $policy = $client->getIamPolicy((new GetIamPolicyRequest)->setResource($name));

    // Update the bindings to include the new member.
    $bindings = $policy->getBindings();
    $bindings[] = new Binding([
        'members' => [$member],
        'role' => 'roles/secretmanager.secretAccessor',
    ]);
    $policy->setBindings($bindings);

    // Build the request.
    $request = (new SetIamPolicyRequest)
        ->setResource($name)
        ->setPolicy($policy);

    // Save the updated policy to the server.
    $client->setIamPolicy($request);

    // Print out a success message.
    printf('Updated IAM policy for %s', $secretId);
}
// [END secretmanager_regional_iam_grant_access]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
