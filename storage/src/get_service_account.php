<?php
/**
 * Copyright 2021 Google LLC
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/storage/README.md
 */

namespace Google\Cloud\Samples\Storage;

# [START storage_get_service_account]
use Google\Cloud\Storage\StorageClient;

/**
 * Get the current service account email.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 */
function get_service_account($projectId)
{
    // $projectId = 'my-project-id';

    $storage = new StorageClient([
        'projectId' => $projectId,
    ]);

    $serviceAccountEmail = $storage->getServiceAccount();

    printf('The GCS service account email for project %s is %s', $projectId, $serviceAccountEmail);
}
# [END storage_get_service_account]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
