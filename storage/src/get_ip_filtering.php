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

# [START storage_get_ip_filtering]
use Google\Cloud\Storage\StorageClient;

/**
 * Retrieve the IP filtering rules for the bucket.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 *        (e.g. 'my-bucket')
 */
function get_ip_filtering(string $bucketName): void
{
    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);

    $info = $bucket->info();

    if (!isset($info['ipFilter'])) {
        printf('Bucket %s has no IP Filter configured.' . PHP_EOL, $bucketName);
        return;
    }

    $ipFilter = $info['ipFilter'];

    printf('IP Filter Configuration for the Bucket %s:' . PHP_EOL, $bucketName);
    printf('Mode: %s' . PHP_EOL, $ipFilter['mode']);

    printf('Allow All Service Agent Access: %s' . PHP_EOL, var_export($ipFilter['allowAllServiceAgentAccess'] ?? false, true));

    if (isset($ipFilter['publicNetworkSource']['allowedIpCidrRanges'])) {
        printf('Allowed Public CIDR Ranges:' . PHP_EOL);
        foreach ($ipFilter['publicNetworkSource']['allowedIpCidrRanges'] as $range) {
            printf('- %s' . PHP_EOL, $range);
        }
    }

    printf('Allow Cross Organization VPCs Access: %s' . PHP_EOL, var_export($ipFilter['allowCrossOrgVpcs'] ?? false, true));

    if (isset($ipFilter['vpcNetworkSources'])) {
        printf('Allowed VPC Network:' . PHP_EOL);
        foreach ($ipFilter['vpcNetworkSources'] as $vpcNetwork) {
            printf('- Network: %s' . PHP_EOL, $vpcNetwork['network']);
            if (isset($vpcNetwork['allowedIpCidrRanges'])) {
                printf('Allowed VPC CIDR Ranges: %s' . PHP_EOL, implode(', ', $vpcNetwork['allowedIpCidrRanges']));
            }
        }
    }
}
# [END storage_get_ip_filtering]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
