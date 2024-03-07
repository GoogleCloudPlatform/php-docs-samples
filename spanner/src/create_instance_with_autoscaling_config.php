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

// [START spanner_create_instance_with_autoscaling_config]
use Google\Cloud\Spanner\Admin\Instance\V1\AutoscalingConfig;
use Google\Cloud\Spanner\Admin\Instance\V1\AutoscalingConfig\AutoscalingLimits;
use Google\Cloud\Spanner\Admin\Instance\V1\AutoscalingConfig\AutoscalingTargets;
use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\CreateInstanceRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\GetInstanceRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\Instance;

/**
 * Creates an instance with autoscaling configuration.
 * Example:
 * ```
 * create_instance_with_autoscaling_config($projectId, $instanceId, $databaseId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 */
function create_instance_with_autoscaling_config(string $projectId, string $instanceId): void
{
    $instanceAdminClient = new InstanceAdminClient();

    $projectName = $instanceAdminClient->projectName($projectId);
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
    $configName = $instanceAdminClient->instanceConfigName($projectId, 'regional-us-central1');
    // Only one of minNodes/maxNodes or minProcessingUnits/maxProcessingUnits
    // can be set. Both min and max need to be set and
    // maxNodes/maxProcessingUnits can be at most 10X of
    // minNodes/minProcessingUnits.
    // highPriorityCpuUtilizationPercent and storageUtilizationPercent are both
    // percentages and must lie between 0 and 100.
    $autoScalingConfig = (new AutoscalingConfig())
        ->setAutoscalingLimits((new AutoscalingLimits())
            ->setMinNodes(1)
            ->setMaxNodes(2))
        ->setAutoscalingTargets((new AutoscalingTargets())
            ->setHighPriorityCpuUtilizationPercent(65)
            ->setStorageUtilizationPercent(95));

    $instance = (new Instance())
        ->setName($instanceName)
        ->setConfig($configName)
        ->setDisplayName('This is a display name.')
        ->setLabels(['cloud_spanner_samples' => true])
        ->setAutoscalingConfig($autoScalingConfig);

    $operation = $instanceAdminClient->createInstance(
        (new CreateInstanceRequest())
        ->setParent($projectName)
        ->setInstanceId($instanceId)
        ->setInstance($instance)
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created instance %s' . PHP_EOL, $instanceId);

    $request = new GetInstanceRequest(['name' => $instanceName]);
    $instanceInfo = $instanceAdminClient->getInstance($request);
    printf(
        'Instance %s has minNodes set to %d.' . PHP_EOL,
        $instanceId,
        $instanceInfo->getAutoscalingConfig()->getAutoscalingLimits()->getMinNodes()
    );
}
// [END spanner_create_instance_with_autoscaling_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
