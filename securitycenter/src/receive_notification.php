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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';
if (count($argv) < 2) {
    return printf('Usage: php %s PROJECT_ID SUSBSCRIPTION_ID\n', basename(__FILE__));
}
list($_, $projectId, $subscriptionId) = $argv;

// [START scc_receive_notifications]
use Google\Cloud\PubSub\PubSubClient;

/** Uncomment and populate these variables in your code */
// $projectId = "{your-project-id}";
// $subscriptionId = "{your-subscription-id}";

$pubsub = new PubSubClient([
    'projectId' => $projectId,
]);
$subscription = $pubsub->subscription($subscriptionId);

foreach ($subscription->pull() as $message) {
    printf('Message: %s' . PHP_EOL, $message->data());
    // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
    $subscription->acknowledge($message);
}

// [END scc_receive_notifications]
