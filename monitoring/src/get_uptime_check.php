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

// [START monitoring_uptime_check_get]
use Google\Cloud\Monitoring\V3\Client\UptimeCheckServiceClient;
use Google\Cloud\Monitoring\V3\GetUptimeCheckConfigRequest;

/**
 * Example:
 * ```
 * get_uptime_check($projectId, $configName);
 * ```
 *
 * @param string $projectId Your project ID
 * @param string $configName
 */
function get_uptime_check($projectId, $configName)
{
    $uptimeCheckClient = new UptimeCheckServiceClient([
        'projectId' => $projectId,
    ]);
    $getUptimeCheckConfigRequest = (new GetUptimeCheckConfigRequest())
        ->setName($configName);

    $uptimeCheck = $uptimeCheckClient->getUptimeCheckConfig($getUptimeCheckConfigRequest);

    print('Retrieved an uptime check:' . PHP_EOL);
    print($uptimeCheck->serializeToJsonString() . PHP_EOL);
}
// [END monitoring_uptime_check_get]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
