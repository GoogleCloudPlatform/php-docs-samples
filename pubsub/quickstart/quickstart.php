<?php

require __DIR__ . '/vendor/autoload.php';

# [START pubsub_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\PubSub\PubSubClient;

# Instantiates a client
$pubsubClient = new PubSubClient();

# The name for the new topic
$topicName = 'my-new-topic';

# Creates the new topic
$topic = $pubsubClient->createTopic($topicName);
# [END pubsub_quickstart]
return $topic;
