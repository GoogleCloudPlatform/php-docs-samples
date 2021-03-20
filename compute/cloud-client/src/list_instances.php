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

// [START list_instances]
use Google\Cloud\Compute\V1\InstancesClient;

/**
 * Creates an instance.
 * Example:
 * ```
 * list_instances($projectId, $zone);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $zone The zone to create the instance in (e.g. "us-central1-a")
 */
function list_instances(string $projectId, string $zone)
{
    // Insert the new Compute Engine instance using the InstancesClient
    $instancesClient = new InstancesClient();
    $instancesList = $instancesClient->list_($projectId, $zone);

    printf('Instances for %s (%s)' . PHP_EOL, $projectId, $zone);
    foreach ($instancesList as $instance) {
        printf(' - %s' . PHP_EOL, $instance->getName());
    }
}
// [END list_instances]

require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
