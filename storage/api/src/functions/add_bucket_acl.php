<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
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

# [START add_bucket_acl]
use Google\Cloud\Storage\StorageClient;

/**
 * Add ACL to a Cloud Storage Bucket.
 *
 * @param string $projectId the project ID of your project
 * @param string $audioFile path to an audio file.
 * @param string $audioFile path to an audio file.
 * @param string $audioFile path to an audio file.
 *
 * @return void
 */
function add_bucket_acl($bucketName, $entity, $role, $options = [])
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $acl = $bucket->acl();
    $acl->add($entity, $role, $options);
    printf('Added %s (%s) to gs://%s ACL' . PHP_EOL, $entity, $role, $bucketName);
}
# [END add_bucket_acl]
