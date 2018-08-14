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

// [START monitoring_uptime_check_create]
use Google\Cloud\Monitoring\V3\UptimeCheckServiceClient;
use Google\Cloud\Monitoring\V3\UptimeCheckConfig;
use Google\Api\MonitoredResource;

/**
 * Example:
 * ```
 * create_uptime_check($projectId, 'myproject.appspot.com', 'Test Uptime Check!');
 * ```
 *
 * @param string $projectId Your project ID
 * @param string $hostName
 * @param string $displayName
 */
function create_uptime_check($projectId, $hostName = 'example.com', $displayName = 'New uptime check')
{
    $uptimeCheckClient = new UptimeCheckServiceClient([
        'projectId' => $projectId,
    ]);

    $monitoredResource = new MonitoredResource();
    $monitoredResource->setType('uptime_url');
    $monitoredResource->setLabels(['host' => $hostName]);

    $uptimeCheckConfig = new UptimeCheckConfig();
    $uptimeCheckConfig->setDisplayName($displayName);
    $uptimeCheckConfig->setMonitoredResource($monitoredResource);

    $uptimeCheckConfig = $uptimeCheckClient->createUptimeCheckConfig(
        $uptimeCheckClient->projectName($projectId),
        $uptimeCheckConfig
    );

    printf('Created an uptime check: %s' . PHP_EOL, $uptimeCheckConfig->getName());
}
// [END monitoring_uptime_check_create]
