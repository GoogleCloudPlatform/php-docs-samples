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

namespace Google\Cloud\Samples\StorageTransfer;

# [START storagetransfer_get_latest_transfer_operation]
use Google\Cloud\StorageTransfer\V1\Client\StorageTransferServiceClient;
use Google\Cloud\StorageTransfer\V1\GetTransferJobRequest;

/**
 * Checks the latest transfer operation for a given transfer job.
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $jobName Storage Transfer Service job name.
 */
function check_latest_transfer_operation(
    string $projectId,
    string $jobName
): void {
    // $project = 'my-project-id';
    // $jobName = 'myJob/1234567890';
    $transferJob = new GetTransferJobRequest([
        'project_id' => $projectId,
        'job_name' => $jobName
    ]);

    $client = new StorageTransferServiceClient();
    $request = $client->getTransferJob($transferJob);
    $latestOperationName = $request->getLatestOperationName();

    if ($latestOperationName) {
        $transferOperation = $client->getOperationsClient()->getOperation($latestOperationName);

        $operation = $transferOperation->getMetadata();

        printf('Latest transfer operation for %s is: %s ' . PHP_EOL, $jobName, $operation->serializeToJsonString());
    } else {
        printf('Transfer job %s has not ran yet.' . PHP_EOL, $jobName);
    }
}
# [END storagetransfer_get_latest_transfer_operation]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
