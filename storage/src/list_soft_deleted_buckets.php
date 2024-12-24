<?php
/**
 * Copyright 2024 Google Inc.
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

namespace Google\Cloud\Samples\Storage;

# [START storage_list_soft_deleted_buckets]
use Google\Cloud\Storage\StorageClient;

/**
 * List all the soft deleted Cloud Storage buckets for the current project.
 */
function list_soft_deleted_buckets(): void
{
    $storage = new StorageClient();
    $options = [ 'softDeleted' => true ];
    foreach ($storage->buckets($options) as $bucket) {
        printf('Bucket: %s' . PHP_EOL, $bucket->name());
    }
}
# [END storage_list_soft_deleted_buckets]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
