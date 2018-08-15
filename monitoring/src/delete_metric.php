<?php
/**
 * Copyright 2016 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/monitoring/README.md
 */

namespace Google\Cloud\Samples\Monitoring;

// [START monitoring_delete_metric]
use Google\Cloud\Monitoring\V3\MetricServiceClient;

/**
 * Example:
 * ```
 * delete_metric($projectId, $databaseId);
 * ```
 *
 * @param string $projectId Your project ID
 * @param string $metricId  The ID of the Metric Descriptor to delete
 */
function delete_metric($projectId, $metricId)
{
    $metrics = new MetricServiceClient([
        'projectId' => $projectId,
    ]);

    $metricPath = $metrics->metricDescriptorName($projectId, $metricId);
    $ret = $metrics->deleteMetricDescriptor($metricPath);

    printf('Deleted a metric: ' . $metricPath . PHP_EOL);
}
// [END monitoring_delete_metric]
