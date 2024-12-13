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

# [START storagetransfer_download_to_posix]

use Google\Cloud\StorageTransfer\V1\Client\StorageTransferServiceClient;
use Google\Cloud\StorageTransfer\V1\CreateTransferJobRequest;
use Google\Cloud\StorageTransfer\V1\GcsData;
use Google\Cloud\StorageTransfer\V1\PosixFilesystem;
use Google\Cloud\StorageTransfer\V1\RunTransferJobRequest;
use Google\Cloud\StorageTransfer\V1\TransferJob;
use Google\Cloud\StorageTransfer\V1\TransferJob\Status;
use Google\Cloud\StorageTransfer\V1\TransferSpec;

/**
 * Creates a request to transfer from the local file system to the sink bucket
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $sinkAgentPoolName The agent pool associated with the POSIX data sink. Defaults to the default agent
 * @param string $gcsSourceBucket Google Cloud Storage source bucket name.
 * @param string $gcsSourcePath An optional path on the Google Cloud Storage bucket to download from.
 * @param string $rootDirectory The root directory path on the destination filesystem.
 */
function posix_download(
    string $projectId,
    string $sinkAgentPoolName,
    string $gcsSourceBucket,
    string $gcsSourcePath,
    string $rootDirectory
): void {
    // $project = 'my-project-id';
    // $sinkAgentPoolName = 'projects/my-project/agentPools/transfer_service_default';
    // $gcsSourceBucket = 'my-gcs-source-bucket';
    // $gcsSourcePath = 'foo/bar/';
    // $rootDirectory = '/directory/to/transfer/source';
    $transferJob = new TransferJob([
        'project_id' => $projectId,
        'transfer_spec' => new TransferSpec([
            'sink_agent_pool_name' => $sinkAgentPoolName,
            'gcs_data_source' => new GcsData([
                'bucket_name' => $gcsSourceBucket,
                'path' => $gcsSourcePath
            ]),
            'posix_data_sink' => new PosixFilesystem(['root_directory' => $rootDirectory])
        ]),
        'status' => Status::ENABLED
    ]);

    $client = new StorageTransferServiceClient();
    $createRequest = (new CreateTransferJobRequest())
        ->setTransferJob($transferJob);
    $response = $client->createTransferJob($createRequest);
    $runRequest = (new RunTransferJobRequest())
        ->setJobName($response->getName())
        ->setProjectId($projectId);
    $client->runTransferJob($runRequest);

    printf('Created and ran a transfer job from %s to %s with name %s ' . PHP_EOL, $gcsSourcePath, $rootDirectory, $response->getName());
}
# [END storagetransfer_download_to_posix]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
