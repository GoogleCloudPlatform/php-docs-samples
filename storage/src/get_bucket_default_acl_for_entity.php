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

# [START get_bucket_default_acl_for_entity]
use Google\Cloud\Storage\StorageClient;

/**
 * Print an entity's role for a bucket's default ACL.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 * @param string $entity The entity to update access controls for.
 *
 * @return Google\Cloud\Storage\Acl the ACL for the Cloud Storage bucket.
 */
function get_bucket_default_acl_for_entity($bucketName, $entity)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $acl = $bucket->defaultAcl();
    $item = $acl->get(['entity' => $entity]);
    printf('%s: %s' . PHP_EOL, $item['entity'], $item['role']);
}
# [END get_bucket_default_acl_for_entity]
