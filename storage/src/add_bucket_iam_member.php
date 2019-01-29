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

# [START add_bucket_iam_member]
use Google\Cloud\Storage\StorageClient;

/**
 * Adds a new member / role IAM pair to a given Cloud Storage bucket.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $role the role you want to add a given member to.
 * @param string $member the member you want to give the new role for the Cloud
 * Storage bucket.
 *
 * @return void
 */
function add_bucket_iam_member($bucketName, $role, $member)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $policy = $bucket->iam()->policy();

    $policy['bindings'][] = [
        'role' => $role,
        'members' => [$member]
    ];

    $bucket->iam()->setPolicy($policy);

    printf('User %s added to role %s for bucket %s' . PHP_EOL, $member, $role, $bucketName);
}
# [END add_bucket_iam_member]
