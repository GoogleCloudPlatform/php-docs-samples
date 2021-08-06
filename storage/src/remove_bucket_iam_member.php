<?php
/**
 * Copyright 2016 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_remove_bucket_iam_member]
use Google\Cloud\Storage\StorageClient;

/**
 * Removes a member / role IAM pair from a given Cloud Storage bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $role The role from which the specified member should be removed.
 * @param string $member The member to be removed from the specified role.
 */
function remove_bucket_iam_member($bucketName, $role, $member)
{
    // $bucketName = 'my-bucket';
    // $role = 'roles/storage.objectViewer';
    // $member = 'group:example@google.com';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $iam = $bucket->iam();
    $policy = $iam->policy(['requestedPolicyVersion' => 3]);
    $policy['version'] = 3;

    foreach ($policy['bindings'] as $i => $binding) {
        // This example only removes member from bindings without a condition.
        if ($binding['role'] == $role && !isset($binding['condition'])) {
            $key = array_search($member, $binding['members']);
            if ($key !== false) {
                unset($binding['members'][$key]);

                // If the last member is removed from the binding, clean up the
                // binding.
                if (count($binding['members']) == 0) {
                    unset($policy['bindings'][$i]);
                    // Ensure array keys are sequential, otherwise JSON encodes
                    // the array as an object, which fails when calling the API.
                    $policy['bindings'] = array_values($policy['bindings']);
                } else {
                    // Ensure array keys are sequential, otherwise JSON encodes
                    // the array as an object, which fails when calling the API.
                    $binding['members'] = array_values($binding['members']);
                    $policy['bindings'][$i] = $binding;
                }

                $iam->setPolicy($policy);
                printf('User %s removed from role %s for bucket %s' . PHP_EOL, $member, $role, $bucketName);
                return;
            }
        }
    }

    throw new \RuntimeException('No matching role-member group(s) found.');
}
# [END storage_remove_bucket_iam_member]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
