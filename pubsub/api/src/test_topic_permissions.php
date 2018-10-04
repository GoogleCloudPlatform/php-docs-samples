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

# [START pubsub_test_topic_permissions]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Prints the permissions of a topic.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 */
function test_topic_permissions($projectId, $topicName)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $topic = $pubsub->topic($topicName);
    $permissions = $topic->iam()->testPermissions([
        'pubsub.topics.attachSubscription',
        'pubsub.topics.publish',
        'pubsub.topics.update'
    ]);
    foreach ($permissions as $permission) {
        printf('Permission: %s' . PHP_EOL, $permission);
    }
}
# [END pubsub_test_topic_permissions]
