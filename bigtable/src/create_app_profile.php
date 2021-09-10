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

// [START bigtable_create_app_profile]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\AppProfile;
use Google\Cloud\Bigtable\Admin\V2\AppProfile\SingleClusterRouting;
use Google\ApiCore\ApiException;

/**
 * Create an App Profile
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $clusterId The ID of the cluster where the new App Profile will route it's requests(in case of single cluster routing)
 * @param string $appProfileId The ID of the App Profile to create
 */
function create_app_profile(
    string $projectId,
    string $instanceId,
    string $clusterId,
    string $appProfileId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    $appProfile = new AppProfile([
        'name' => $appProfileId,
        'description' => 'Description for this newly created AppProfile'
    ]);

    // create a new routing policy
    // allow_transactional_writes refers to Single-Row-Transactions(https://cloud.google.com/bigtable/docs/app-profiles#single-row-transactions)
    $routingPolicy = new SingleClusterRouting([
        'cluster_id' => $clusterId,
        'allow_transactional_writes' => false
    ]);

    // set the newly created routing policy to our app profile
    $appProfile->setSingleClusterRouting($routingPolicy);

    // we could also create a multi cluster routing policy like so:
    // $routingPolicy = new \Google\Cloud\Bigtable\Admin\V2\AppProfile\MultiClusterRoutingUseAny();
    // $appProfile->setMultiClusterRoutingUseAny($routingPolicy);

    printf('Creating a new AppProfile %s' . PHP_EOL, $appProfileId);

    try {
        $newAppProfile = $instanceAdminClient->createAppProfile($instanceName, $appProfileId, $appProfile);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'ALREADY_EXISTS') {
            printf('AppProfile %s already exists.', $appProfileId);
            return;
        }
        throw $e;
    }

    printf('AppProfile created: %s', $newAppProfile->getName());
}
// [END bigtable_create_app_profile]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
