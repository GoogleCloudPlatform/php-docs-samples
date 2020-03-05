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

# [START storage_remove_bucket_owner]
use Google\Cloud\Storage\StorageClient;

/**
 * Delete an entity from a bucket's default ACL.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $entity the name of the entity to remove from the ACL.
 * @param array $options
 *
 * @return void
 */
function delete_bucket_acl($bucketName, $entity, $options = [])
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $acl = $bucket->acl();
    $acl->delete($entity, $options);
    printf('Deleted %s from gs://%s ACL' . PHP_EOL, $entity, $bucketName);
}
# [END storage_remove_bucket_owner]
