<?php
/**
 * Copyright 2020 Google LLC.
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

namespace Google\Cloud\Samples\SecurityCenter;

// [START securitycenter_receive_notifications]
use Google\Cloud\PubSub\PubSubClient;

/**
 * @param string $projectId             Your Cloud Project ID
 * @param string $subscriptionId        Your subscription ID
 */
function receive_notification(string $projectId, string $subscriptionId): void
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $subscription = $pubsub->subscription($subscriptionId);

    foreach ($subscription->pull() as $message) {
        printf('Message: %s' . PHP_EOL, $message->data());
        // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
        $subscription->acknowledge($message);
    }
}
// [END securitycenter_receive_notifications]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
