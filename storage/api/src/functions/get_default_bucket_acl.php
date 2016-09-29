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

# [START get_default_bucket_acl]
use Google\Cloud\Storage\StorageClient;

/**
 * Print all entities and roles for a bucket's default ACL.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 *
 * @return Google\Cloud\Storage\Acl the ACL for the Cloud Storage bucket.
 */
function get_default_bucket_acl($bucketName)
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $acl = $bucket->defaultAcl();
    foreach ($acl->get() as $item) {
        printf('%s: %s' . PHP_EOL, $item['entity'], $item['role']);
    }
}
# [END get_default_bucket_acl]
