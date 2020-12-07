<?php
/**
 * Copyright 2017 Google Inc.
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

# [START storage_download_file_requester_pays]
use Google\Cloud\Storage\StorageClient;

/**
 * Download file using specified project as requester
 *
 * @param string $projectId Your Google Cloud billable project ID.
 * @param string $bucketName A Google Cloud Storage bucket name.
 * @param string $objectName Name of object in Google Cloud Storage to download locally.
 * @param string $destination Path to local file to save.
 *
 * @return void
 */
function download_file_requester_pays($projectId, $bucketName, $objectName, $destination)
{
    $storage = new StorageClient([
        'projectId' => $projectId
    ]);
    $userProject = true;
    $bucket = $storage->bucket($bucketName, $userProject);
    $object = $bucket->object($objectName);
    $object->downloadToFile($destination);
    printf('Downloaded gs://%s/%s to %s using requester-pays requests.' . PHP_EOL,
        $bucketName, $objectName, basename($destination));
}
# [END storage_download_file_requester_pays]
