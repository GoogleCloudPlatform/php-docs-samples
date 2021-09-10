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

// [START bigtable_delete_app_profile]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\ApiCore\ApiException;

/**
 * Delete an App Profile from a Bigtable instance.
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $appProfileId The ID of the App Profile to be deleted
 */
function delete_app_profile(
    string $projectId,
    string $instanceId,
    string $appProfileId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $appProfileName = $instanceAdminClient->appProfileName($projectId, $instanceId, $appProfileId);
    $ignoreWarnings = true;

    printf('Deleting the App Profile: %s' . PHP_EOL, $appProfileId);

    try {
        // If $ignoreWarnings is set to false, Bigtable will warn you that all future requests using the AppProfile will fail
        $instanceAdminClient->deleteAppProfile($appProfileName, $ignoreWarnings);
        printf('App Profile %s deleted.' . PHP_EOL, $appProfileId);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('App Profile %s does not exist.' . PHP_EOL, $appProfileId);
            return;
        }
        throw $e;
    }
}
// [END bigtable_delete_app_profile]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
