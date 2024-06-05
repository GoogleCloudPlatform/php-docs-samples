<?php
/**
 * Copyright 2024 Google LLC
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

namespace Google\Cloud\Samples\StorageControl;

# [START storage_control_get_folder]
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\GetFolderRequest;

/**
 * Get a folder in an existing bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $folderName The name of your folder inside the bucket.
 *        (e.g. 'my-folder')
 */
function get_folder(string $bucketName, string $folderName): void
{
    $storageControlClient = new StorageControlClient();

    // Set project to "_" to signify global bucket
    $formattedName = $storageControlClient->folderName('_', $bucketName, $folderName);

    $request = new GetFolderRequest([
        'name' => $formattedName,
    ]);

    $folder = $storageControlClient->getFolder($request);

    printf($folder->getName());
}
# [END storage_control_get_folder]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
