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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_list_buckets_ip_filtering]
use Google\Cloud\Storage\StorageClient;

/**
 * Lists all buckets including their IP filtering status.
 *
 * @param string $projectId The ID of your Google Cloud project.
 *        (e.g. 'my-project-id')
 */
function list_buckets_ip_filtering(string $projectId): void
{
    $storage = new StorageClient([
        'projectId' => $projectId
    ]);

    printf('Buckets:' . PHP_EOL);
    foreach ($storage->buckets(['projection' => 'full']) as $bucket) {
        $info = $bucket->info();
        $mode = $info['ipFilter']['mode'] ?? 'Not Configured';

        printf('Bucket Name: %s, IP Filtering Mode: %s' . PHP_EOL, $bucket->name(), $mode);
    }
}
# [END storage_list_buckets_ip_filtering]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
