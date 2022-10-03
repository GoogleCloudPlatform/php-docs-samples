<?php
/**
 * Copyright 2022 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/spanner/README.md
 */

namespace Google\Cloud\Samples\Spanner;

// [START spanner_create_instance_config]
use Google\Cloud\Spanner\Admin\Instance\V1\ReplicaInfo;
use Google\Cloud\Spanner\SpannerClient;

/**
 * Creates a customer managed instance configuration.
 * Example:
 * ```
 * create_instance_config($instanceConfigId);
 * ```
 *
 * @param string $userConfigId The customer managed instance configuration id. The id must start with 'custom-'.
 * @param string $baseConfigId Base configuration ID to be used for creation, e.g. nam11.
 */
function create_instance_config($userConfigId, $baseConfigId)
{
    $spanner = new SpannerClient();

    // Get a Google Managed instance configuration to use as the base for our custom instance configuration.
    $baseInstanceConfig = $spanner->instanceConfiguration(
        $baseConfigId
    );

    $instanceConfiguration = $spanner->instanceConfiguration($userConfigId);
    $operation = $instanceConfiguration->create(
        $baseInstanceConfig,
        array_merge(
            $baseInstanceConfig->info()['replicas'],
            // The replicas for the custom instance configuration must include all the replicas of the base
            // configuration, in addition to at least one from the list of optional replicas of the base
            // configuration.
            [new ReplicaInfo(
                [
                    'location' => 'us-east1',
                    'type' => ReplicaInfo\ReplicaType::READ_ONLY,
                    'default_leader_location' => false
                ]
            )]
        ),
        [
            'displayName' => 'This is a display name',
            'labels' => [
                'php_cloud_spanner_samples' => true,
            ]
        ]
    );

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Created instance configuration %s' . PHP_EOL, $userConfigId);
}
// [END spanner_create_instance_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
