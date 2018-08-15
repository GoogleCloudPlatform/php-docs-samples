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

// [START monitoring_uptime_check_update]
use Google\Cloud\Monitoring\V3\UptimeCheckServiceClient;
use Google\Protobuf\FieldMask;

/**
 * Example:
 * ```
 * update_uptime_checks($projectId);
 * ```
 */
function update_uptime_checks($projectId, $configName, $newDisplayName = null, $newHttpCheckPath = null)
{
    $uptimeCheckClient = new UptimeCheckServiceClient([
        'projectId' => $projectId,
    ]);

    $uptimeCheck = $uptimeCheckClient->getUptimeCheckConfig($displayName);
    $fieldMask = new FieldMask();
    if ($newDisplayName) {
        $fieldMask->getPaths()[] = 'display_name';
        $uptimeCheck->setDisplayName($newDisplayName);
    }
    if ($newHttpCheckPath) {
        $fieldMask->getPaths()[] = 'http_check.path';
        $uptimeCheck->getHttpCheck()->setPath($newHttpCheckPath);
    }

    $uptimeCheckClient->updateUptimeCheckConfig($uptimeCheck, $fieldMask);

    print($uptimeCheck->serializeToString() . PHP_EOL);
}
// [END monitoring_uptime_check_update]
