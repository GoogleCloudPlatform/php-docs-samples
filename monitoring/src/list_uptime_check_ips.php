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

// [START monitoring_uptime_check_list_ips]
use Google\Cloud\Monitoring\V3\UptimeCheckServiceClient;

/**
 * Example:
 * ```
 * list_uptime_check_ips($projectId);
 * ```
 */
function list_uptime_check_ips($projectId)
{
    $uptimeCheckClient = new UptimeCheckServiceClient([
        'projectId' => $projectId,
    ]);

    $pages = $uptimeCheckClient->listUptimeCheckIps();

    foreach ($pages->iteratePages() as $page) {
        $ips = $page->getResponseObject()->getUptimeCheckIps();
        foreach ($ips as $ip) {
            printf(
                'ip address: %s, region: %s, location: %s' . PHP_EOL,
                $ip->getIpAddress(),
                $ip->getRegion(),
                $ip->getLocation()
            );
        }
    }
}
// [END monitoring_uptime_check_list_ips]
