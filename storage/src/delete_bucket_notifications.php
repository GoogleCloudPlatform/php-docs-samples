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

# [START storage_delete_bucket_notification]
use Google\Cloud\Storage\StorageClient;

/**
 * Deletes a notification configuration for a bucket.
 * This sample is used on this page:
 *   https://cloud.google.com/storage/docs/reporting-changes
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $notificationId The ID of the notification.
 */
function delete_bucket_notifications(
    string $bucketName,
    string $notificationId
): void {
    // $bucketName = 'your-bucket';
    // $notificationId = 'your-notification-id';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $notification = $bucket->notification($notificationId);
    $notification->delete();

    printf(
        'Successfully deleted notification with ID %s for bucket %s' . PHP_EOL,
        $notification->id(),
        $bucketName
    );
}
# [END storage_delete_bucket_notification]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
