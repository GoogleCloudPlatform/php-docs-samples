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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/storage/api/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START remove_bucket_iam_member]
use Google\Cloud\Storage\StorageClient;

/**
 * Adds a new member / role IAM pair to a given Cloud Storage bucket.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $role the role you want to remove a given member from.
 * @param string $member the member you want to remove from the given role.
 *
 * @return void
 */
function remove_bucket_iam_member($bucketName, $role, $member)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $policy = $bucket->iam()->policy();

    foreach ($policy['bindings'] as $i => &$binding) {
        if ($binding['role'] == $role) {
            if (false !== $j = array_search($member, $binding['members'])) {
                unset($binding['members'][$j]);
                $binding['members'] = array_values($binding['members']);
                if (empty($binding['members'])) {
                    unset($policy['bindings'][$i]);
                    $policy['bindings'] = array_values($policy['bindings']);
                }
                var_dump($policy);
                $bucket->iam()->setPolicy($policy);
                printf('Removed %s from role %s for bucket %s.' . PHP_EOL, $member, $role, $bucketName);
                return;
            } else {
                printf('Member %s not found for role %s for bucket %s.' . PHP_EOL, $member, $role, $bucketName);
            }
        }
    }
    printf('Role %s not found for bucket %s.' . PHP_EOL, $role, $bucketName);   
}
# [END remove_bucket_iam_member]
