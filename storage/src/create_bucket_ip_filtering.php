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

namespace Google\Cloud\Samples\Storage;

# [START storage_create_bucket_ip_filtering]
use Google\Cloud\Storage\StorageClient;

/**
 * Create a new bucket with an IP filtering configuration.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * (e.g. 'my-new-bucket')
 */
function create_bucket_ip_filtering(string $bucketName): void
{
    $storage = new StorageClient();

    // Use 'Disabled' during tests or initial setup to prevent immediate lockout.
    $storage->createBucket($bucketName, [
        'ipFilter' => [
            'mode' => 'Disabled',
            'publicNetworkSource' => [
                'allowedIpCidrRanges' => ['1.2.3.0/24']
            ],
            'allowAllServiceAgentAccess' => true
        ]
    ]);

    printf('Bucket %s created with IP filtering rules.' . PHP_EOL, $bucketName);
}
# [END storage_create_bucket_ip_filtering]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
