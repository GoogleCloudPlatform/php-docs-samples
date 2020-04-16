<?php
/**
 * Copyright 2020 Google Inc.
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

# [START storage_remove_bucket_conditional_iam_binding]
use Google\Cloud\Storage\StorageClient;

/**
 * Removes a conditional IAM binding from a bucket's IAM policy.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $role the role that will be given to members in this binding.
 * @param string $title condition's title
 * @param string $description condition's description
 * @param string $expression the condition specified in CEL expression language.
 *
 * To see how to express a condition in CEL, visit:
 * @see https://cloud.google.com/storage/docs/access-control/iam#conditions.
 *
 * @return void
 */
function remove_bucket_conditional_iam_binding($bucketName, $role, $title, $description, $expression)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $policy = $bucket->iam()->policy(['requestedPolicyVersion' => 3]);

    $policy['version'] = 3;

    $key_of_conditional_binding = null;
    foreach ($policy['bindings'] as $key => $binding) {
        if ($binding['role'] == $role && isset($binding['condition'])) {
            $condition = $binding['condition'];
            if ($condition['title'] == $title
                 && $condition['description'] == $description
                 && $condition['expression'] == $expression) {
                $key_of_conditional_binding = $key;
                break;
            }
        }
    }

    if ($key_of_conditional_binding != null) {
        unset($policy['bindings'][$key_of_conditional_binding]);
        // Ensure array keys are sequential, otherwise JSON encodes
        // the array as an object, which fails when calling the API.
        $policy['bindings'] = array_values($policy['bindings']);
        $bucket->iam()->setPolicy($policy);
        print('Conditional Binding was removed.' . PHP_EOL);
    } else {
        print('No matching conditional binding found.' . PHP_EOL);
    }
}
# [END storage_remove_bucket_conditional_iam_binding]
