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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/monitoring/README.md
 */

namespace Google\Cloud\Samples\Monitoring;

// [START monitoring_list_descriptors]
use Google\Cloud\Monitoring\V3\Client\MetricServiceClient;
use Google\Cloud\Monitoring\V3\ListMetricDescriptorsRequest;

/**
 * Example:
 * ```
 * list_descriptors($projectId);
 * ```
 *
 * @param string $projectId Your project ID
 */
function list_descriptors($projectId)
{
    $metrics = new MetricServiceClient([
        'projectId' => $projectId,
    ]);

    $projectName = $metrics->projectName($projectId);
    $listMetricDescriptorsRequest = (new ListMetricDescriptorsRequest())
        ->setName($projectName);
    $descriptors = $metrics->listMetricDescriptors($listMetricDescriptorsRequest);

    printf('Metric Descriptors:' . PHP_EOL);
    foreach ($descriptors->iterateAllElements() as $descriptor) {
        printf($descriptor->getName() . PHP_EOL);
    }
}
// [END monitoring_list_descriptors]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
