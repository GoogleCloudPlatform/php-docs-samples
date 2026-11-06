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

# [START storage_get_object_contexts]
use Google\Cloud\Storage\StorageClient;

/**
 * Retrieve contexts for an existing object.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $objectName The name of your Cloud Storage object.
 *        (e.g. 'my-object')
 */
function get_object_contexts(string $bucketName, string $objectName): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);

    $info = $object->info();
    if (isset($info['contexts']['custom'])) {
        printf('Contexts for object %s:' . PHP_EOL, $objectName);
        foreach ($info['contexts']['custom'] as $key => $data) {
            printf(' - Key: %s, Value: %s' . PHP_EOL, $key, $data['value'] ?? '');
            printf(' - Created: %s' . PHP_EOL, $data['createTime'] ?? '');
            printf(' - Updated: %s' . PHP_EOL, $data['updateTime'] ?? '');
        }
    }
}
# [END storage_get_object_contexts]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
