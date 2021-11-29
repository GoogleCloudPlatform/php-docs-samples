<?php
/**
 * Copyright 2019 Google LLC.
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

// [START bigtable_delete_instance]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\ApiCore\ApiException;

/**
 * Delete a bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance to be deleted
 */
function delete_instance(
    string $projectId,
    string $instanceId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    printf('Deleting Instance' . PHP_EOL);
    try {
        $instanceAdminClient->deleteInstance($instanceName);
        printf('Deleted Instance: %s.' . PHP_EOL, $instanceId);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Instance %s does not exists.' . PHP_EOL, $instanceId);
        } else {
            throw $e;
        }
    }
}
// [END bigtable_delete_instance]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
