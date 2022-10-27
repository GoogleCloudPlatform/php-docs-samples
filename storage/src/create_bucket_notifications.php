<?php
/**
 * Copyright 2022 Google LLC
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

# [START storage_create_bucket_notifications]
use Google\Cloud\Storage\StorageClient;

/**
 * Creates a notification configuration for a bucket.
 * This sample is used on this page:
 *   https://cloud.google.com/storage/docs/reporting-changes
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $topicName The name of the topic you would like to create a notification.
 */
function create_bucket_notifications(
    string $bucketName,
    string $topicName
): void {
    // $bucketName = 'my-bucket';
    // $topicName = 'my-topic';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $notification = $bucket->createNotification($topicName);

    printf(
        'Successfully created notification with ID %s for bucket %s in topic %s' . PHP_EOL,
        $notification->id(),
        $bucketName,
        $topicName
    );
}
# [END storage_create_bucket_notifications]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
