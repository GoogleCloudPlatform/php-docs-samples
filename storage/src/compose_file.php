<?php
/**
 * Copyright 2021 Google LLC
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

# [START storage_compose_file]
use Google\Cloud\Storage\StorageClient;

/**
 * Compose two objects into a single target object.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $firstObjectName The name of the first GCS object to compose.
 * @param string $secondObjectName The name of the second GCS object to compose.
 * @param string $targetObjectName The name of the object to be created.
 */
function compose_file($bucketName, $firstObjectName, $secondObjectName, $targetObjectName)
{
    // $bucketName = 'my-bucket';
    // $firstObjectName = 'my-object-1';
    // $secondObjectName = 'my-object-2';
    // $targetObjectName = 'composed-my-object-1-my-object-2';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    // In this example, we are composing only two objects, but Cloud Storage supports
    // composition of up to 32 objects.
    $objectsToCompose = [$firstObjectName, $secondObjectName];

    $targetObject = $bucket->compose($objectsToCompose, $targetObjectName, [
        'destination' => [
            'contentType' => 'application/octet-stream'
        ]
    ]);

    if ($targetObject->exists()) {
        printf(
            'New composite object %s was created by combining %s and %s',
            $targetObject->name(),
            $firstObjectName,
            $secondObjectName
        );
    }
}
# [END storage_compose_file]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
