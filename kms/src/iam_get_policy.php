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

declare(strict_types=1);

namespace Google\Cloud\Samples\Kms;

// [START kms_iam_get_policy]
use Google\Cloud\Iam\V1\GetIamPolicyRequest;
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;

function iam_get_policy(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the resource name.
    $resourceName = $client->cryptoKeyName($projectId, $locationId, $keyRingId, $keyId);

    // The resource name could also be a key ring.
    // $resourceName = $client->keyRingName($projectId, $locationId, $keyRingId);

    // Get the current IAM policy.
    $getIamPolicyRequest = (new GetIamPolicyRequest())
        ->setResource($resourceName);
    $policy = $client->getIamPolicy($getIamPolicyRequest);

    // Print the policy.
    printf('IAM policy for %s' . PHP_EOL, $resourceName);
    foreach ($policy->getBindings() as $binding) {
        printf('%s' . PHP_EOL, $binding->getRole());

        foreach ($binding->getMembers() as $member) {
            printf('- %s' . PHP_EOL, $member);
        }
    }

    return $policy;
}
// [END kms_iam_get_policy]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
