<?php
/**
 * Copyright 2026 Google LLC
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

# [START storage_delete_object_contexts]
use Google\Cloud\Storage\StorageClient;

/**
 * Delete all custom contexts from an existing object.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $objectName The name of your Cloud Storage object.
 *        (e.g. 'my-object')
 */
function delete_object_contexts(string $bucketName, string $objectName): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);

    // Fetch the current metageneration so the update can be made conditional.
    $info = $object->info();

    // To delete all contexts, set the 'custom' key to null, which is sent as a
    // JSON null and clears every context. Do not use an empty PHP array: it
    // serializes as a JSON list ([]) rather than an empty map.
    // Optional: set a metageneration-match precondition to avoid potential
    // race conditions and data corruptions. The request to update is aborted
    // if the object's metageneration does not match your precondition.
    $object->update([
        'contexts' => [
            'custom' => null,
        ],
    ], [
        'ifMetagenerationMatch' => $info['metageneration'],
    ]);

    printf('Deleted object contexts for object %s in bucket %s.' . PHP_EOL, $objectName, $bucketName);
}
# [END storage_delete_object_contexts]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
