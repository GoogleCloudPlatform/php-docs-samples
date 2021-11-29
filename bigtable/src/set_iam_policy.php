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

// [START bigtable_set_iam_policy]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\ApiCore\ApiException;
use Google\Cloud\Iam\V1\Binding;
use Google\Cloud\Iam\V1\Policy;

/**
 * Set the IAM policy for a Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $email The email of the member to be assigned the role(Format: 'user:EMAIL_ID')
 * @param string $role The role to be assigned. For a list of roles check out https://cloud.google.com/bigtable/docs/access-control
 */
function set_iam_policy(
    string $projectId,
    string $instanceId,
    string $email,
    string $role = 'roles/bigtable.user'
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    try {
        $policy = new Policy([
            'bindings' => [
                new Binding([
                    'role' => $role,
                    'members' => [$email]
                ])
            ]
        ]);

        $iamPolicy = $instanceAdminClient->setIamPolicy($instanceName, $policy);

        foreach ($iamPolicy->getBindings() as $binding) {
            foreach ($binding->getmembers() as $member) {
                printf('%s:%s' . PHP_EOL, $binding->getRole(), $member);
            }
        }
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Instance %s does not exist.' . PHP_EOL, $instanceId);
            return;
        }
        throw $e;
    }
}
// [END bigtable_set_iam_policy]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
