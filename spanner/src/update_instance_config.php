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

// [START spanner_update_instance_config]
use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\InstanceConfig;
use Google\Cloud\Spanner\Admin\Instance\V1\UpdateInstanceConfigRequest;
use Google\Protobuf\FieldMask;

/**
 * Updates a customer managed instance configuration.
 * Example:
 * ```
 * update_instance_config($instanceConfigId);
 * ```
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $instanceConfigId The customer managed instance configuration id. The id must start with 'custom-'.
 */
function update_instance_config(string $projectId, string $instanceConfigId): void
{
    $instanceAdminClient = new InstanceAdminClient();

    $instanceConfigPath = $instanceAdminClient->instanceConfigName($projectId, $instanceConfigId);
    $displayName = 'New display name';

    $instanceConfig = new InstanceConfig();
    $instanceConfig->setName($instanceConfigPath);
    $instanceConfig->setDisplayName($displayName);
    $instanceConfig->setLabels(['cloud_spanner_samples' => true, 'updated' => true]);

    $fieldMask = new FieldMask();
    $fieldMask->setPaths(['display_name', 'labels']);

    $updateInstanceConfigRequest = (new UpdateInstanceConfigRequest())
        ->setInstanceConfig($instanceConfig)
        ->setUpdateMask($fieldMask);

    $operation = $instanceAdminClient->updateInstanceConfig($updateInstanceConfigRequest);

    print('Waiting for operation to complete...' . PHP_EOL);
    $operation->pollUntilComplete();

    printf('Updated instance configuration %s' . PHP_EOL, $instanceConfigId);
}
// [END spanner_update_instance_config]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
