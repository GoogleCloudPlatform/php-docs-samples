<?php
/**
 * Copyright 2016 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/pubsub/api/README.md
 */

namespace Google\Cloud\Samples\PubSub;

# [START pubsub_set_subscription_policy]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Adds a user to the policy for a Pub/Sub subscription.
 *
 * @param string $projectId  The Google project ID.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 * @param string $userEmail  The user email to add to the policy.
 */
function set_subscription_policy($projectId, $subscriptionName, $userEmail)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $subscription = $pubsub->subscription($subscriptionName);
    $policy = $subscription->iam()->policy();
    $policy['bindings'][] = [
        'role' => 'roles/pubsub.subscriber',
        'members' => ['user:' . $userEmail]
    ];
    $subscription->iam()->setPolicy($policy);

    printf('User %s added to policy for %s' . PHP_EOL,
        $userEmail,
        $subscriptionName);
}
# [END pubsub_set_subscription_policy]
