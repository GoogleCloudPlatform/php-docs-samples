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

# [START storagetransfer_manifest_request]

use Google\Cloud\StorageTransfer\V1\Client\StorageTransferServiceClient;
use Google\Cloud\StorageTransfer\V1\CreateTransferJobRequest;
use Google\Cloud\StorageTransfer\V1\GcsData;
use Google\Cloud\StorageTransfer\V1\PosixFilesystem;
use Google\Cloud\StorageTransfer\V1\RunTransferJobRequest;
use Google\Cloud\StorageTransfer\V1\TransferJob;
use Google\Cloud\StorageTransfer\V1\TransferJob\Status;
use Google\Cloud\StorageTransfer\V1\TransferManifest;
use Google\Cloud\StorageTransfer\V1\TransferSpec;

/**
 * Creates and runs a transfer from the local file system to the sink bucket
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $sourceAgentPoolName The agent pool associated with the POSIX data source.
 * @param string $rootDirectory The root directory path on the source filesystem.
 * @param string $sinkGcsBucketName The name of the GCS bucket to transfer objects to.
 * @param string $manifestLocation Transfer manifest location. Must be a `gs:` URL.
 */
function manifest_request(
    string $projectId,
    string $sourceAgentPoolName,
    string $rootDirectory,
    string $sinkGcsBucketName,
    string $manifestLocation
): void {
    // $project = 'my-project-id';
    // $sourceAgentPoolName = 'projects/my-project/agentPools/transfer_service_default';
    // $rootDirectory = '/directory/to/transfer/source';
    // $sinkGcsBucketName = 'my-sink-bucket';
    // $manifestLocation = 'gs://my-bucket/sample_manifest.csv';
    $transferJob = new TransferJob([
        'project_id' => $projectId,
        'transfer_spec' => new TransferSpec([
            'source_agent_pool_name' => $sourceAgentPoolName,
            'posix_data_source' => new PosixFilesystem(['root_directory' => $rootDirectory]),
            'gcs_data_sink' => new GcsData(['bucket_name' => $sinkGcsBucketName]),
            'transfer_manifest' => new TransferManifest(['location' => $manifestLocation])
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

    printf('Created and ran transfer job from %s to %s using manifest %s with name %s ' . PHP_EOL, $rootDirectory, $sinkGcsBucketName, $manifestLocation, $response->getName());
}
# [END storagetransfer_manifest_request]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
