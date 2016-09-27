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

# [START delete_object_acl]
use Google\Cloud\Storage\StorageClient;

/**
 * Delete an entity from the ACL of a Cloud Storage file.
 *
 * @param string $objectName name of the object to delete.
 * @param array $options
 *
 * @return void
 */
function delete_object_acl($bucketName, $objectName, $entity, $options = [])
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $acl = $object->acl();
    $acl->delete($entity, $options);
    printf('Deleted %s from gs://%s/%s ACL.' . PHP_EOL, $entity, $bucketName, $objectName);
}
# [END delete_object_acl]
