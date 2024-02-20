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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_list_instance_configs]

use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\ListInstanceConfigsRequest;

/**
 * Lists the available instance configurations.
 * Example:
 * ```
 * list_instance_configs();
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 */
function list_instance_configs(string $projectId = null): void
{
    $instanceAdminClient = new InstanceAdminClient();
    $projectName = InstanceAdminClient::projectName($projectId);
    $request = new ListInstanceConfigsRequest();
    $request->setParent($projectName);
    $resp = $instanceAdminClient->listInstanceConfigs($request);
    foreach ($resp as $element) {
        printf(
            'Available leader options for instance config %s: %s' . PHP_EOL,
            $element->getDisplayName(),
            implode(',', iterator_to_array($element->getLeaderOptions()))
        );
    }
}
// [END spanner_list_instance_configs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
