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

// [START spanner_list_instance_config_operations]
use Google\Cloud\Spanner\Admin\Instance\V1\Client\InstanceAdminClient;
use Google\Cloud\Spanner\Admin\Instance\V1\ListInstanceConfigOperationsRequest;
use Google\Cloud\Spanner\Admin\Instance\V1\UpdateInstanceConfigMetadata;
use Google\Cloud\Spanner\Admin\Instance\V1\CreateInstanceConfigMetadata;

/**
 * Lists the instance configuration operations for a project.
 * Example:
 * ```
 * list_instance_config_operations($projectId);
 * ```
 *
 * @param $projectId The Google Cloud Project ID.
 */
function list_instance_config_operations(string $projectId): void
{
    $instanceAdminClient = new InstanceAdminClient();
    $projectName = InstanceAdminClient::projectName($projectId);
    $listInstanceConfigOperationsRequest = (new ListInstanceConfigOperationsRequest())
        ->setParent($projectName);

    $instanceConfigOperations = $instanceAdminClient->listInstanceConfigOperations(
        $listInstanceConfigOperationsRequest
    );

    foreach ($instanceConfigOperations->iterateAllElements() as $instanceConfigOperation) {
        $type = $instanceConfigOperation->getMetadata()->getTypeUrl();
        if (strstr($type, 'CreateInstanceConfigMetadata')) {
            $obj = new CreateInstanceConfigMetadata();
        } else {
            $obj = new UpdateInstanceConfigMetadata();
        }

        printf(
            'Instance config operation for %s of type %s has status %s.' . PHP_EOL,
            $instanceConfigOperation->getMetadata()->unpack($obj)->getInstanceConfig()->getName(),
            $type,
            $instanceConfigOperation->getDone() ? 'done' : 'running'
        );
    }
}
// [END spanner_list_instance_config_operations]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
