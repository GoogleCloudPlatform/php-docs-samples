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

# [START storage_add_bucket_iam_member]
use Google\Cloud\Storage\StorageClient;

/**
 * Adds a new member / role IAM pair to a given Cloud Storage bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $role The role to which the given member should be added.
 * @param string[] $members The member(s) to be added to the role.
 */
function add_bucket_iam_member($bucketName, $role, array $members)
{
    // $bucketName = 'my-bucket';
    // $role = 'roles/storage.objectViewer';
    // $members = ['group:example@google.com'];

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $policy = $bucket->iam()->policy(['requestedPolicyVersion' => 3]);
    $policy['version'] = 3;

    $policy['bindings'][] = [
        'role' => $role,
        'members' => $members
    ];

    $bucket->iam()->setPolicy($policy);

    printf('Added the following member(s) to role %s for bucket %s' . PHP_EOL, $role, $bucketName);
    foreach ($members as $member) {
        printf('    %s' . PHP_EOL, $member);
    }
}
# [END storage_add_bucket_iam_member]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
