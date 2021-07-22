<?php

namespace Google\Cloud\Samples\Bigtable;

/**
 * Copyright 2019 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/README.md
 */

// [START bigtable_get_app_profile]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\ApiCore\ApiException;

/**
 * Get the App Profile
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $appProfileId The ID of the App Profile to fetch
 */
function get_app_profile(
    string $projectId,
    string $instanceId,
    string $appProfileId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $appProfileName = $instanceAdminClient->appProfileName($projectId, $instanceId, $appProfileId);

    printf("Fetching the App Profile %s" . PHP_EOL, $appProfileId);
    try {
        $appProfile = $instanceAdminClient->getAppProfile($appProfileName);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("App profile %s does not exist." . PHP_EOL, $appProfileId);
            return;
        } else {
            throw $e;
        }
    }

    printf("Printing Details:" . PHP_EOL);

    // Fetch some commonly used metadata
    echo "Name: " . $appProfile->getName() . PHP_EOL;
    echo "Etag: " . $appProfile->getEtag() . PHP_EOL;
    echo "Description: " . $appProfile->getDescription() . PHP_EOL;
    echo "Routing Policy: " . $appProfile->getRoutingPolicy() . PHP_EOL;

    if ($appProfile->hasSingleClusterRouting()) {
        echo "Cluster: " . $appProfile->getSingleClusterRouting()->getClusterId() . PHP_EOL;
        echo "Single-Row Transactions: " . ($appProfile->getSingleClusterRouting()->getAllowTransactionalWrites() ? "Yes" : "No") . PHP_EOL;
    }
}
// [END bigtable_get_app_profile]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
