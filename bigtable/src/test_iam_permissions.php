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

// [START bigtable_test_iam_permissions]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;

/**
 * Test IAM permissions for the current caller
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 */
function test_iam_permissions(
    string $projectId,
    string $instanceId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    // The set of permissions to check for the `resource`. Permissions with
    // wildcards (such as '*' or 'bigtable.*') are not allowed. For more
    // information see
    // [IAM Overview](https://cloud.google.com/iam/docs/overview#permissions)
    $permissions = ['bigtable.clusters.create', 'bigtable.tables.create', 'bigtable.tables.list'];

    $response = $instanceAdminClient->testIamPermissions($instanceName, $permissions);

    // This array will contain the permissions that are passed for the current caller
    foreach ($response->getPermissions() as $permission) {
        printf($permission . PHP_EOL);
    }
}
// [END bigtable_test_iam_permissions]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
