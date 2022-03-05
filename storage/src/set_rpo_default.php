<?php
/**
 * Copyright 2021 Google LLC
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

# [START storage_set_rpo_default]
use Google\Cloud\Storage\StorageClient;

/**
 * Set the bucket's replication behavior or recovery point objective (RPO) to `DEFAULT`.
 *
 * @param string $bucketName the name of your Cloud Storage bucket.
 */
function set_rpo_default($bucketName)
{
    // $bucketName = 'my-bucket';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $rpo = 'DEFAULT';

    // Updating the rpo value of a multi-region bucket to DEFAULT has no effect
    // and updating the rpo value of a regional bucket will throw an exception.
    $bucket->update([
        'rpo' => $rpo
    ]);

    printf(
        'The replication behavior or recovery point objective (RPO) has been set to DEFAULT for %s.' . PHP_EOL,
        $bucketName
    );
}
# [END storage_set_rpo_default]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
