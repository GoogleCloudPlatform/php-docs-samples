<?php

# [START pubsub_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\PubSub\PubSubClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$pubsub = new PubSubClient([
    'projectId' => $projectId
]);

# The name for the new topic
$topicName = 'my-new-topic';

# Creates the new topic
$topic = $pubsub->createTopic($topicName);

echo 'Topic ' . $topic->name() . ' created.';
# [END pubsub_quickstart]
return $topic;
