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

# [START storage_enable_ip_filtering]
use Google\Cloud\Storage\StorageClient;

/**
 * Enable IP filtering on a bucket.
 *
 * @param string $projectId The ID of your Google Cloud project.
 *        (e.g. 'my-project-id')
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 * @param string $mode The mode to set IP filtering to.
 *        (e.g. 'Enabled')
 */
function enable_ip_filtering(string $projectId, string $bucketName, string $mode = 'Enabled'): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $ipFilter = [
        'mode' => $mode,
        'allowAllServiceAgentAccess' => true,
        'publicNetworkSource' => [
            'allowedIpCidrRanges' => ['1.2.3.0/24']
        ],
        'vpcNetworkSources' => [
            [
                'network' => sprintf('projects/%s/global/networks/default', $projectId),
                'allowedIpCidrRanges' => ['10.0.0.0/24']
            ]
        ]
    ];

    $info = $bucket->update(['ipFilter' => $ipFilter]);

    printf(
        'Enabled IP filtering Rules for the Bucket: %s' . PHP_EOL,
        $bucketName
    );
}
# [END storage_enable_ip_filtering]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
