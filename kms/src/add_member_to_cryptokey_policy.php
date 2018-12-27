<?php
/**
 * Copyright 2018 Google Inc.
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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/kms/README.md
 */


// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 7) {
    return printf("Usage: php %s PROJECT_ID LOCATION_ID KEYRING_ID CRYPTOKEY_ID MEMBER ROLE\n", basename(__FILE__));
}
list($_, $projectId, $locationId, $keyRingId, $cryptoKeyId, $member, $role) = $argv;


# [START kms_add_member_to_cryptokey_policy]
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Cloud\Iam\V1\Binding;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $locationId = 'The location ID of the crypto key. Can be "global", "us-west1", etc.';
// $keyRingId = 'The KMS key ring ID';
// $cryptoKeyId = 'The KMS key ID';
// $member = 'Must be in the format "user:$userEmail" or "serviceAccount:$serviceAccountEmail"';
// $role = 'Must be in the format "roles/$role", "organizations/$organizationId/roles/$role", or "projects/$projectId/roles/$role"';

$kms = new KeyManagementServiceClient();

// The resource name of the CryptoKey.
$cryptoKeyName = $kms->cryptoKeyName($projectId, $locationId, $keyRingId, $cryptoKeyId);

// Get the current IAM policy and add the new account to it.
$policy = $kms->getIamPolicy($cryptoKeyName);
$bindings = $policy->getBindings();
$bindings[] = new Binding([
    'members' => [$member],
    'role' => $role,
]);
$policy->setBindings($bindings);

// Set the new IAM Policy.
$kms->setIamPolicy($cryptoKeyName, $policy);

printf('Member %s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL, $member, $cryptoKeyId, $keyRingId);
# [END kms_add_member_to_cryptokey_policy]
