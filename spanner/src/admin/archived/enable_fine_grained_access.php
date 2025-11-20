<?php
/**
 * Copyright 2023 Google Inc.
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

// [START spanner_enable_fine_grained_access]
use \Google\Cloud\Iam\V1\Binding;
use \Google\Type\Expr;
use Google\Cloud\Iam\V1\GetIamPolicyRequest;
use Google\Cloud\Iam\V1\SetIamPolicyRequest;
use Google\Cloud\Spanner\Admin\Database\V1\Client\DatabaseAdminClient;

/**
 * Enable Fine Grained Access.
 * Example:
 * ```
 * enable_fine_grained_access($projectId, $instanceId, $databaseId, $iamMember, $databaseRole, $title);
 * ```
 *
 * @param string $projectId The Google cloud project ID
 * @param string $instanceId The Spanner instance ID.
 * @param string $databaseId The Spanner database ID.
 * @param string $iamMember The IAM member. Eg: `user:{emailid}`,
 *        `serviceAccount:{emailid}`, `group:{emailid}`, `domain:{domain}`
 * @param string $databaseRole The database role bound to
 *        the IAM member.
 * @param string $title Condition title.
 */
function enable_fine_grained_access(
    string $projectId,
    string $instanceId,
    string $databaseId,
    string $iamMember,
    string $databaseRole,
    string $title
): void {
    $adminClient = new DatabaseAdminClient();
    $resource = $adminClient->databaseName($projectId, $instanceId, $databaseId);
    $getIamPolicyRequest = (new GetIamPolicyRequest())
        ->setResource($resource);
    $policy = $adminClient->getIamPolicy($getIamPolicyRequest);

    // IAM conditions need at least version 3
    if ($policy->getVersion() != 3) {
        $policy->setVersion(3);
    }

    $binding = new Binding([
        'role' => 'roles/spanner.fineGrainedAccessUser',
        'members' => [$iamMember],
        'condition' => new Expr([
            'title' => $title,
            'expression' => sprintf("resource.name.endsWith('/databaseRoles/%s')", $databaseRole)
        ])
    ]);
    $policy->setBindings([$binding]);
    $setIamPolicyRequest = (new SetIamPolicyRequest())
        ->setResource($resource)
        ->setPolicy($policy);
    $adminClient->setIamPolicy($setIamPolicyRequest);

    printf('Enabled fine-grained access in IAM' . PHP_EOL);
}
// [END spanner_enable_fine_grained_access]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
