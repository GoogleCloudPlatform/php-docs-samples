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

# [START storagetransfer_transfer_to_nearline]

use DateTime;
use Google\Cloud\StorageTransfer\V1\Client\StorageTransferServiceClient;
use Google\Cloud\StorageTransfer\V1\CreateTransferJobRequest;
use Google\Cloud\StorageTransfer\V1\GcsData;
use Google\Cloud\StorageTransfer\V1\ObjectConditions;
use Google\Cloud\StorageTransfer\V1\RunTransferJobRequest;
use Google\Cloud\StorageTransfer\V1\Schedule;
use Google\Cloud\StorageTransfer\V1\TransferJob;
use Google\Cloud\StorageTransfer\V1\TransferJob\Status;
use Google\Cloud\StorageTransfer\V1\TransferOptions;
use Google\Cloud\StorageTransfer\V1\TransferSpec;
use Google\Protobuf\Duration as ProtobufDuration;
use Google\Type\Date;
use Google\Type\TimeOfDay;

/**
 * Create a daily migration from a GCS bucket to another GCS bucket for objects untouched for 30+ days.
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $description A useful description for your transfer job.
 * @param string $sourceGcsBucketName The name of the GCS bucket to transfer objects from.
 * @param string $sinkGcsBucketName The name of the GCS bucket to transfer objects to.
 * @param string $startDate Date to start daily migration.
 */
function nearline_request(
    string $projectId,
    string $description,
    string $sourceGcsBucketName,
    string $sinkGcsBucketName,
    string $startDate
): void {
    // $project = 'my-project-id';
    // $description = 'My transfer job';
    // $sourceGcsBucketName = 'my-source-bucket';
    // $sinkGcsBucketName = 'my-sink-bucket';
    // $startDate = new DateTime();

    $dateTime = new DateTime($startDate);
    $date = new Date([
        'year' => $dateTime->format('Y'),
        'month' => $dateTime->format('m'),
        'day' => $dateTime->format('d'),
    ]);

    $time = new TimeOfDay([
        'hours' => $dateTime->format('H'),
        'minutes' => $dateTime->format('i'),
        'seconds' => $dateTime->format('s'),
    ]);

    $transferJob = new TransferJob([
        'project_id' => $projectId,
        'description' => $description,
        'schedule' => new Schedule([
            'schedule_start_date' => $date,
            'start_time_of_day' => $time
        ]),
        'transfer_spec' => new TransferSpec([
            'gcs_data_source' => new GcsData(['bucket_name' => $sourceGcsBucketName]),
            'gcs_data_sink' => new GcsData(['bucket_name' => $sinkGcsBucketName]),
            'object_conditions' => new ObjectConditions([
                'min_time_elapsed_since_last_modification' => new ProtobufDuration([
                    'seconds' => 2592000
                ])
            ]),
            'transfer_options' => new TransferOptions(['delete_objects_from_source_after_transfer' => true])
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

    printf('Created and ran transfer job : %s' . PHP_EOL, $response->getName());
}
# [END storagetransfer_transfer_to_nearline]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
