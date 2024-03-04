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

// [START spanner_create_instance_config]
use Google\Cloud\Spanner\Admin\Instance\V1\CreateInstanceConfigRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\GetInstanceConfigRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\InstanceConfig;
use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\ReplicaInfo;

/**
 * Creates a customer managed instance configuration.
 * Example:
 * ```
 * create_instance_config($instanceConfigId);
 * ```
 *
 * @param string $projectId The Google Cloud Project ID.
 * @param string $instanceConfigId The customer managed instance configuration id. The id must start with 'custom-'.
 * @param string $baseConfigId Base configuration ID to be used for creation, e.g. nam11.
 */
function create_instance_config(string $projectId, string $instanceConfigId, string $baseConfigId): void
{
    $instanceAdminClient = new InstanceAdminClient();
    $projectName = InstanceAdminClient::projectName($projectId);
    $instanceConfigName = $instanceAdminClient->instanceConfigName(
        $projectId,
        $instanceConfigId
    );

    // Get a Google Managed instance configuration to use as the base for our custom instance configuration.
    $baseInstanceConfig = $instanceAdminClient->instanceConfigName(
        $projectId,
        $baseConfigId
    );

    $request = new GetInstanceConfigRequest(['name' => $baseInstanceConfig]);
    $baseInstanceConfigInfo = $instanceAdminClient->getInstanceConfig($request);

    $instanceConfig = (new InstanceConfig())
        ->setBaseConfig($baseInstanceConfig)
        ->setName($instanceConfigName)
        ->setDisplayName('My custom instance configuration')
        ->setLabels(['php-cloud-spanner-samples' => true])
        ->setReplicas(array_merge(
            iterator_to_array($baseInstanceConfigInfo->getReplicas()),
            [new ReplicaInfo([
            'location' => 'us-east1',
            'type' => ReplicaInfo\ReplicaType::READ_ONLY,
            'default_leader_location' => false
            ])]
        ));

    $request = new CreateInstanceConfigRequest([
        'parent' => $projectName,
        'instance_config' => $instanceConfig,
        'instance_config_id' => $instanceConfigId
    ]);
    $operation = $instanceAdminClient->createInstanceConfig($request);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created instance configuration %s' . PHP_EOL, $instanceConfigId);
}
// [END spanner_create_instance_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
