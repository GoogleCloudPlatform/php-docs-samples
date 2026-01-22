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

# [START pubsub_topic_with_smt]
# Includes the autoloader for libraries installed with composer
require_once __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\PubSub\PubSubClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$pubsub = new PubSubClient([
    'projectId' => $projectId
]);

# Configure the Single Message Transform function
$smtConfig = [
    'javascriptUdf' => [
        'functionName' => 'toUpper',
        'code' => 'function toUpper(message, metadata){
            message.data = message.data.toUpperCase();
            return message;
        }'
    ]
];

# Add the SMT to the Topic Configuration
$topicConfig = [
    'messageTransforms' => [
        $smtConfig
    ]
];

# The new Topic name
$topicName = 'smt-topic';

# Create the new Topic with the name and the configuration
$topic = $pubsub->createTopic($topicName, $topicConfig);
echo 'Created a topic with SMT: ' . $topic->name();
# [END pubsub_topic_with_smt]

return $topic;
