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

# [START storage_print_pubsub_bucket_notification]

use Google\Cloud\Storage\StorageClient;

/**
 * Lists notification configurations for a bucket.
 * This sample is used on this page:
 *   https://cloud.google.com/storage/docs/reporting-changes
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $notificationId The ID of the notification.
 */
function print_pubsub_bucket_notification(
    string $bucketName,
    string $notificationId
): void {
    // $bucketName = 'your-bucket';
    // $notificationId = 'your-notification-id';

    $storage = new StorageClient();
    $bucket = $storage->bucket($bucketName);
    $notification = $bucket->notification($notificationId);
    $notificationInfo = $notification->info();

    printf('Notification ID: %s' . PHP_EOL, $notification->id());
    printf('Event Types: %s' . PHP_EOL, $notificationInfo['event_types'] ?? '');
    printf('Custom Attributes: %s' . PHP_EOL, $notificationInfo['custom_attributes'] ?? '');
    printf('Payload Format: %s' . PHP_EOL, $notificationInfo['payload_format']);
    printf('Blob Name Prefix: %s' . PHP_EOL, $notificationInfo['blob_name_prefix'] ?? '');
    printf('Etag: %s' . PHP_EOL, $notificationInfo['etag']);
    printf('Self Link: %s' . PHP_EOL, $notificationInfo['selfLink']);
}
# [END storage_print_pubsub_bucket_notification]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
