<?php

/**
 * Copyright 2025 Google LLC
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/storagebatchoperations/README.md
 */

namespace Google\Cloud\Samples\StorageBatchOperations;

# [START storage_batch_list_jobs]
use Google\Cloud\StorageBatchOperations\V1\Client\StorageBatchOperationsClient;
use Google\Cloud\StorageBatchOperations\V1\ListJobsRequest;

/**
 * List Jobs in a given project.
 *
 * @param string $projectId Your Google Cloud project ID.
 *        (e.g. 'my-project-id')
 */
function list_jobs(string $projectId): void
{
    // Create a client.
    $storageBatchOperationsClient = new StorageBatchOperationsClient();

    $parent = $storageBatchOperationsClient->locationName($projectId, 'global');

    $request = new ListJobsRequest([
        'parent' => $parent,
    ]);

    $jobs = $storageBatchOperationsClient->listJobs($request);

    foreach ($jobs as $job) {
        printf('Job name: %s' . PHP_EOL, $job->getName());
    }
}
# [END storage_batch_list_jobs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
