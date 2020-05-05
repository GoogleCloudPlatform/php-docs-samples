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

// [START kms_iam_add_member]
use Google\Cloud\Iam\V1\Binding;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;

function iam_add_member_sample(
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

    // Add the member to the policy.
    $bindings = $policy->getBindings();
    $bindings[] = (new Binding())
        ->setRole('roles/cloudkms.cryptoKeyEncrypterDecrypter')
        ->setMembers([$member]);
    $policy->setBindings($bindings);

    // Save the updated IAM policy.
    $updatedPolicy = $client->setIamPolicy($resourceName, $policy);
    printf('Added %s' . PHP_EOL, $member);
    return $updatedPolicy;
}
// [END kms_iam_add_member]

if (isset($argv)) {
    if (count($argv) === 0) {
        return printf("Usage: php %s PROJECT_ID LOCATION_ID KEY_RING_ID KEY_ID MEMBER\n", basename(__FILE__));
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    list($_, $projectId, $locationId, $keyRingId, $keyId, $member) = $argv;
    iam_add_member_sample($projectId, $locationId, $keyRingId, $keyId, $member);
}
