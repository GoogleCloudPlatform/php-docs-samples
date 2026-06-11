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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storagecontrol/README.md
 */

namespace Google\Cloud\Samples\StorageControl;

# [START storage_control_delete_folder_recursive]
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\DeleteFolderRecursiveRequest;

/**
 * Delete a folder and its contents in an existing bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $folderName The name of your folder inside the bucket.
 *        (e.g. 'my-folder')
 */
function delete_folder_recursive(string $bucketName, string $folderName): void
{
    $storageControlClient = new StorageControlClient();

    // Set project to "_" to signify global bucket
    $formattedName = $storageControlClient->folderName('_', $bucketName, $folderName);

    $request = new DeleteFolderRecursiveRequest([
        'name' => $formattedName,
    ]);

    $operation = $storageControlClient->deleteFolderRecursive($request);

    $operation->pollUntilComplete();

    if ($operation->operationSucceeded()) {
        printf('Deleted folder: %s', $folderName);
    } else {
        $error = $operation->getError();
        printf('Failed to delete folder: %s', $error ? $error->getMessage() : 'Unknown error');
    }
}
# [END storage_control_delete_folder_recursive]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
