<?php
/**
 * Copyright 2021 Google Inc.
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

# [START storagetransfer_quickstart]
use Google\Cloud\StorageTransfer\V1\StorageTransferServiceClient;
use Google\Cloud\StorageTransfer\V1\TransferJob;
use Google\Cloud\StorageTransfer\V1\TransferJob\Status;
use Google\Cloud\StorageTransfer\V1\TransferSpec;
use Google\Cloud\StorageTransfer\V1\GcsData;

/**
 * Creates and runs a transfer job between two GCS buckets
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $sourceGcsBucketName The name of the GCS bucket to transfer objects from.
 * @param string $sinkGcsBucketName The name of the GCS bucket to transfer objects to.
 */
function quickstart($projectId, $sourceGcsBucketName, $sinkGcsBucketName)
{
    // $project = 'my-project-id';
    // $sourceGcsBucketName = 'my-source-bucket';
    // $sinkGcsBucketName = 'my-sink-bucket';
    $transferJob = new TransferJob([
        'project_id' => $projectId,
        'transfer_spec' => new TransferSpec([
            'gcs_data_sink' => new GcsData(['bucket_name' => $sourceGcsBucketName]),
            'gcs_data_source' => new GcsData(['bucket_name' => $sourceGcsBucketName])
        ]),
        'status' => Status::ENABLED
    ]);

    $client = new StorageTransferServiceClient();
    $response = $client->createTransferJob($transferJob);
    $client->runTransferJob($response->getName(), $projectId);

    printf('Created and ran transfer job from %s to %s with name %s ' . PHP_EOL, $sourceGcsBucketName, $sinkGcsBucketName, $response->getName());
}
# [END storagetransfer_quickstart]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
