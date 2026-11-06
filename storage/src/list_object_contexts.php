<?php
/**
 * Copyright 2026 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_list_object_contexts]
use Google\Cloud\Storage\StorageClient;

/**
 *  List objects with a filter based on contexts.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 */
function list_object_contexts(string $bucketName): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    // Examples of filters based on contexts:
    // 1. Objects with a specific key attached: 'contexts:"department"'
    // 2. Objects WITHOUT a specific key attached: '-contexts:"department"'
    // 3. Objects with a specific key-value pair: 'contexts."department"="finance"'
    // 4. Objects WITHOUT a specific key-value pair: '-contexts."department"="finance"'
    $options = [
        'filter' => 'contexts."department"="finance"'
    ];
    foreach ($bucket->objects($options) as $object) {
        printf('Found object: %s' . PHP_EOL, $object->name());
    }
}
# [END storage_list_object_contexts]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
