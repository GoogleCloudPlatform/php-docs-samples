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

// [START spanner_create_instance]
use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\CreateInstanceRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\Instance;

/**
 * Creates an instance.
 * Example:
 * ```
 * create_instance($projectId, $instanceId);
 * ```
 *
 * @param string $projectId  The Spanner project ID.
 * @param string $instanceId The Spanner instance ID.
 */
function create_instance(string $projectId, string $instanceId): void
{
    $instanceAdminClient = new InstanceAdminClient();
    $parent = InstanceAdminClient::projectName($projectId);
    $instanceName = InstanceAdminClient::instanceName($projectId, $instanceId);
    $configName = $instanceAdminClient->instanceConfigName($projectId, 'regional-us-central1');
    $instance = (new Instance())
        ->setName($instanceName)
        ->setConfig($configName)
        ->setDisplayName('dispName')
        ->setNodeCount(1);

    $operation = $instanceAdminClient->createInstance(
        (new CreateInstanceRequest())
        ->setParent($parent)
        ->setInstanceId($instanceId)
        ->setInstance($instance)
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created instance %s' . PHP_EOL, $instanceId);
}
// [END spanner_create_instance]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
