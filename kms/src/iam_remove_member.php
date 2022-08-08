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

// [START kms_iam_remove_member]
use Google\Cloud\Iam\V1\Binding;
use Google\Cloud\Iam\V1\Policy;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

function iam_remove_member(
    string $projectId = 'my-project',
    string $locationId = 'us-east1',
    string $keyRingId = 'my-key-ring',
    string $keyId = 'my-key',
    string $member = 'user:foo@example.com'
) {
    // Create the Cloud KMS client.
    $client = new KeyManagementServiceClient();

    // Build the resource name.
    $resourceName = $client->cryptoKeyName($projectId, $locationId, $keyRingId, $keyId);

    // The resource name could also be a key ring.
    // $resourceName = $client->keyRingName($projectId, $locationId, $keyRingId);

    // Get the current IAM policy.
    $policy = $client->getIamPolicy($resourceName);

    // Remove the member from the policy by creating a new policy with everyone
    // but the member to remove.
    $newPolicy = new Policy();
    foreach ($policy->getBindings() as $binding) {
        if ($binding->getRole() !== 'roles/cloudkms.cryptoKeyEncrypterDecrypter') {
            $newPolicy->getBindings()[] = $binding;
        } else {
            $newBinding = (new Binding())
              ->setRole($binding->getRole());

            $newMembers = [];
            foreach ($binding->getMembers() as $existingMember) {
                if ($member !== $existingMember) {
                    $newMembers[] = $existingMember;
                }
            }

            $newPolicy->getBindings()[] = (new Binding())
              ->setRole($binding->getRole())
              ->setMembers($newMembers);
        }
    }

    // Save the updated IAM policy.
    $updatedPolicy = $client->setIamPolicy($resourceName, $newPolicy);
    printf('Removed %s' . PHP_EOL, $member);

    return $updatedPolicy;
}
// [END kms_iam_remove_member]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
return \Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
