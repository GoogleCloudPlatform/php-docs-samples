<?php
/**
 * Copyright 2021 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/compute/cloud-client/README.md
 */

namespace Google\Cloud\Samples\Compute;

# [START compute_instances_list]
use Google\Cloud\Compute\V1\InstancesClient;

/**
 * List all instances for a particular Cloud project and zone.
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $zone Zone to list instances for (like "us-central1-a").
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function list_instances(string $projectId, string $zone)
{
    // List Compute Engine instances using InstancesClient.
    $instancesClient = new InstancesClient();
    $instancesList = $instancesClient->list($projectId, $zone);

    printf('Instances for %s (%s)' . PHP_EOL, $projectId, $zone);
    foreach ($instancesList as $instance) {
        printf(' - %s' . PHP_EOL, $instance->getName());
    }
}
# [END compute_instances_list]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
