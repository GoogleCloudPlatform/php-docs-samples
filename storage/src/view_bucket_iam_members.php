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

# [START storage_view_bucket_iam_members]
use Google\Cloud\Storage\StorageClient;

/**
 * View Bucket IAM members for a given Cloud Storage bucket.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 *
 * @return void
 */
function view_bucket_iam_members($bucketName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $policy = $bucket->iam()->policy(['requestedPolicyVersion' => 3]);

    printf('Printing Bucket IAM members for Bucket: %s' . PHP_EOL, $bucketName);
    printf(PHP_EOL);

    foreach ($policy['bindings'] as $binding) {
        printf('Role: %s' . PHP_EOL, $binding['role']);
        printf('Members:' . PHP_EOL);
        foreach ($binding['members'] as $member) {
            printf('  %s' . PHP_EOL, $member);
        }

        if (isset($binding['condition'])) {
            $condition = $binding['condition'];
            printf('  with condition:' . PHP_EOL);
            printf('    Title: %s' . PHP_EOL, $condition['title']);
            printf('    Description: %s' . PHP_EOL, $condition['description']);
            printf('    Expression: %s' . PHP_EOL, $condition['expression']);
        }
        printf(PHP_EOL);
    }
}
# [END storage_view_bucket_iam_members]
