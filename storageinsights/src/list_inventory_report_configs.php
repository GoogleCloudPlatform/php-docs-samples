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

namespace Google\Cloud\Samples\StorageInsights;

# [START storageinsights_list_inventory_report_configs]
use Google\Cloud\StorageInsights\V1\StorageInsightsClient;

/**
 * Example:
 * ```
 * list_inventory_report_configs($projectId, $location);
 * ```
 *
 * @param string $projectId Your Google Cloud Project ID
 * @param string $location The location to list configs in
 */
function list_inventory_report_configs(string $projectId, string $location): void
{
    $storageInsightsClient = new StorageInsightsClient();

    $formattedParent = $storageInsightsClient->locationName($projectId, $location);
    $configs = $storageInsightsClient->listReportConfigs($formattedParent);

    printf('Inventory report configs in project %s and location %s:' . PHP_EOL, $projectId, $location);
    foreach ($configs->iterateAllElements() as $config) {
        printf('%s' . PHP_EOL, $config->getName());
    }
}
# [END storageinsights_list_inventory_report_configs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
