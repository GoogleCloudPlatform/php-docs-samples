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

# [START storage_batch_create_job]
use Google\Cloud\StorageBatchOperations\V1\Client\StorageBatchOperationsClient;
use Google\Cloud\StorageBatchOperations\V1\CreateJobRequest;
use Google\Cloud\StorageBatchOperations\V1\Job;
use Google\Cloud\StorageBatchOperations\V1\BucketList;
use Google\Cloud\StorageBatchOperations\V1\BucketList\Bucket;
use Google\Cloud\StorageBatchOperations\V1\PrefixList;
use Google\Cloud\StorageBatchOperations\V1\DeleteObject;

/**
 * Create a new batch job.
 *
 * @param string $projectId Your Google Cloud project ID.
 *        (e.g. 'my-project-id')
 * @param string $jobId A unique identifier for this job.
 *        (e.g. '94d60cc1-2d95-41c5-b6e3-ff66cd3532d5')
 * @param string $bucketName The name of your Cloud Storage bucket to operate on.
 *        (e.g. 'my-bucket')
 * @param string $objectPrefix The prefix of objects to include in the operation.
 *        (e.g. 'prefix1')
 */
function create_job(string $projectId, string $jobId, string $bucketName, string $objectPrefix): void
{
    // Create a client.
    $storageBatchOperationsClient = new StorageBatchOperationsClient();

    $parent = $storageBatchOperationsClient->locationName($projectId, 'global');

    $prefixListConfig = new PrefixList(['included_object_prefixes' => [$objectPrefix]]);
    $bucket = new Bucket(['bucket' => $bucketName, 'prefix_list' => $prefixListConfig]);
    $bucketList = new BucketList(['buckets' => [$bucket]]);

    $deleteObject = new DeleteObject(['permanent_object_deletion_enabled' => false]);

    $job = new Job(['bucket_list' => $bucketList, 'delete_object' => $deleteObject]);

    $request = new CreateJobRequest([
        'parent' => $parent,
        'job_id' => $jobId,
        'job' => $job,
    ]);
    $response = $storageBatchOperationsClient->createJob($request);

    printf('Created job: %s', $response->getName());
}
# [END storage_batch_create_job]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
