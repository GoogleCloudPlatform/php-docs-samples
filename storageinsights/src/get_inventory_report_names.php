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

# [START storageinsights_get_inventory_report_names]
use Google\Cloud\StorageInsights\V1\StorageInsightsClient;

/**
 * Gets an existing inventory report config.
 * Example:
 * ```
 * get_inventory_report_names($projectId, $bucketLocation, $inventoryReportConfigUuid);
 * ```
 *
 * @param string $projectId Your Google Cloud Project ID
 * @param string $bucketLocation The location your bucket is in
 * @param string $inventoryReportConfigUuid The UUID of the inventory report you want to get file names for
 */
function get_inventory_report_names(
    string $projectId,
    string $bucketLocation,
    string $inventoryReportConfigUuid
): void {
    $storageInsightsClient = new StorageInsightsClient();

    $reportConfigName = $storageInsightsClient->reportConfigName($projectId, $bucketLocation, $inventoryReportConfigUuid);
    $reportConfig = $storageInsightsClient->getReportConfig($reportConfigName);
    $extension = $reportConfig->hasCsvOptions() ? 'csv' : 'parquet';
    print('You can use the Google Cloud Storage Client '
        . 'to download the following objects from Google Cloud Storage:' . PHP_EOL);
    $listReportConfigs = $storageInsightsClient->listReportDetails($reportConfig->getName());
    foreach ($listReportConfigs->iterateAllElements() as $reportDetail) {
        for ($index = $reportDetail->getShardsCount() - 1; $index >= 0; $index--) {
            printf('%s%d.%s' . PHP_EOL, $reportDetail->getReportPathPrefix(), $index, $extension);
        }
    }
}
# [END storageinsights_get_inventory_report_names]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
