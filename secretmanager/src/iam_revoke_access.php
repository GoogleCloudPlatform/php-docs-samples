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

// [START secretmanager_iam_revoke_access]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;

/**
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 * @param string $member Your member (e.g. 'user:foo@example.com')
 */
function iam_revoke_access(string $projectId, string $secretId, string $member): void
{
    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient();

    // Build the resource name of the secret.
    $name = $client->secretName($projectId, $secretId);

    // Get the current IAM policy.
    $policy = $client->getIamPolicy($name);

    // Remove the member from the list of bindings.
    foreach ($policy->getBindings() as $binding) {
        if ($binding->getRole() == 'roles/secretmanager.secretAccessor') {
            $members = $binding->getMembers();
            foreach ($members as $i => $existingMember) {
                if ($member == $existingMember) {
                    unset($members[$i]);
                    $binding->setMembers($members);
                    break;
                }
            }
        }
    }

    // Save the updated policy to the server.
    $client->setIamPolicy($name, $policy);

    // Print out a success message.
    printf('Updated IAM policy for %s', $secretId);
}
// [END secretmanager_iam_revoke_access]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
