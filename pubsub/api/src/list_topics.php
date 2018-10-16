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

# [START pubsub_list_topics]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Lists all Pub/Sub topics.
 *
 * @param string $projectId  The Google project ID.
 */
function list_topics($projectId)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    foreach ($pubsub->topics() as $topic) {
        printf('Topic: %s' . PHP_EOL, $topic->name());
    }
}
# [END pubsub_list_topics]
