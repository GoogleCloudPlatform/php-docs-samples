<?php
/**
 * Copyright 2020 Google LLC.
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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';
if (count($argv) < 1) {
    return printf("Usage: php %s ORGANIZATION_ID\n",  basename(__FILE__));
}
list($_, $organizationId) = $argv;

// [START scc_list_notification_configs]
use Google\Cloud\SecurityCenter\V1\SecurityCenterClient;

/** Uncomment and populate these variables in your code */
// $organizationId = "{your-org-id}";

$securityCenterClient = new SecurityCenterClient();
$organizationName = "organizations/" . $organizationId;

try {
    $pagedResponse = $securityCenterClient->listNotificationConfigs($organizationName);
    $count = 0;
    foreach ($pagedResponse->iterateAllElements() as $element) {
        $count += 1;
    }

    printf("Notification configs were listed");
    return $count;

} finally {
    $securityCenterClient->close();
}

// [END scc_list_notification_configs]