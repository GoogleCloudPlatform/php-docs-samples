<?php
/**
 * Copyright 2018 Google Inc.
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

// [START monitoring_get_resource]
use Google\Cloud\Monitoring\V3\MetricServiceClient;

/**
 * Example:
 * ```
 * get_resource('your-project-id', 'gcs_bucket');
 * ```
 *
 * @param string $projectId Your project ID
 * @param string $resourceType The resource type of the monitored resource.
 */
function get_resource($projectId, $resourceType)
{
    $metrics = new MetricServiceClient([
        'projectId' => $projectId,
    ]);

    $metricName = $metrics->monitoredResourceDescriptorName($projectId, $resourceType);
    $resource = $metrics->getMonitoredResourceDescriptor($metricName);

    printf('Name: %s' . PHP_EOL, $resource->getName());
    printf('Type: %s' . PHP_EOL, $resource->getType());
    printf('Display Name: %s' . PHP_EOL, $resource->getDisplayName());
    printf('Description: %s' . PHP_EOL, $resource->getDescription());
    printf('Labels:' . PHP_EOL);
    foreach ($resource->getLabels() as $labels) {
        printf('  %s (%s) - %s' . PHP_EOL,
            $labels->getKey(),
            $labels->getValueType(),
            $labels->getDescription());
    }
}
// [END monitoring_get_resource]
