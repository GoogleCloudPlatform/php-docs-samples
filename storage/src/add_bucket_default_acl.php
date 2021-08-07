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

# [START storage_add_bucket_default_owner]
use Google\Cloud\Storage\StorageClient;

/**
 * Add an entity and role to a bucket's default ACL.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $entity The entity for which to update access controls.
 * @param string $role The permissions to add for the specified entity.
 */
function add_bucket_default_acl($bucketName, $entity, $role)
{
    // $bucketName = 'my-bucket';
    // $entity = 'user-example@domain.com';
    // $role = 'OWNER';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $acl = $bucket->defaultAcl();
    $acl->add($entity, $role);
    printf('Added %s (%s) to gs://%s default ACL' . PHP_EOL, $entity, $role, $bucketName);
}
# [END storage_add_bucket_default_owner]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
