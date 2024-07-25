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

// [START spanner_create_instance_partition]
use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\CreateInstancePartitionRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\InstancePartition;

/**
 * Creates an instance partition.
 * Example:
 * ```
 * create_instance_partition($projectId, $instanceId, $instancePartitionId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceId The Spanner instance ID.
 * @param string $instancePartitionId The instance partition ID.
 */
function create_instance_partition(string $projectId, string $instanceId, string $instancePartitionId): void
{
    $instanceAdminClient = new InstanceAdminClient();

    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
    $instancePartitionName = $instanceAdminClient->instancePartitionName($projectId, $instanceId, $instancePartitionId);
    $configName = $instanceAdminClient->instanceConfigName($projectId, 'nam3');

    $instancePartition = (new InstancePartition())
        ->setConfig($configName)
        ->setDisplayName('Test instance partition.')
        ->setNodeCount(1);

    $operation = $instanceAdminClient->createInstancePartition(
        (new CreateInstancePartitionRequest())
        ->setParent($instanceName)
        ->setInstancePartitionId($instancePartitionId)
        ->setInstancePartition($instancePartition)
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created instance partition %s' . PHP_EOL, $instancePartitionId);
}
// [END spanner_create_instance_partition]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
