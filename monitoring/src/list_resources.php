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

// [START monitoring_list_resources]
use Google\Cloud\Monitoring\V3\MetricServiceClient;

/**
 * Example:
 * ```
 * list_resources('your-project-id');
 * ```
 *
 * @param string $projectId Your project ID
 */
function list_resources($projectId)
{
    $metrics = new MetricServiceClient([
        'projectId' => $projectId,
    ]);
    $projectName = $metrics->projectName($projectId);
    $descriptors = $metrics->listMonitoredResourceDescriptors($projectName);
    foreach ($descriptors->iterateAllElements() as $descriptor) {
        print($descriptor->getType() . PHP_EOL);
    }
}
// [END monitoring_list_resources]
