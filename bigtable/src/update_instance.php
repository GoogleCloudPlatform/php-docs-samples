<?php
/**
 * Copyright 2021 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/README.md
 */

namespace Google\Cloud\Samples\Bigtable;

// [START bigtable_update_instance]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Protobuf\FieldMask;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\ApiCore\ApiException;

/**
 * Update a Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance to be updated
 * @param string $newDisplayName The new display name of the instance
 */
function update_instance(
    string $projectId,
    string $instanceId,
    string $newDisplayName
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    $newType = InstanceType::PRODUCTION;
    $newLabels = [
        'new-label-key' => 'label-val'
    ];

    $instance = new Instance([
        'name' => $instanceName,
        'display_name' => $newDisplayName,
        'labels' => $newLabels,
        'type' => $newType
    ]);

    // This specifies the fields that need to be updated from $instance
    $updateMask = new FieldMask([
        'paths' => ['labels', 'type', 'display_name']
    ]);

    try {
        $operationResponse = $instanceAdminClient->partialUpdateInstance($instance, $updateMask);

        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $updatedInstance = $operationResponse->getResult();
            printf('Instance updated with the new display name: %s.' . PHP_EOL, $updatedInstance->getDisplayName());
        // doSomethingWith($updatedInstance)
        } else {
            $error = $operationResponse->getError();
            // handleError($error)
        }
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Instance %s does not exist.' . PHP_EOL, $instanceId);
            return;
        }
        throw $e;
    }
}
// [END bigtable_update_instance]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
