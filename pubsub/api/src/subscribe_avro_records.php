<?php
/**
 * Copyright 2021 Google LLC
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/main/pubsub/api/README.md
 */
namespace Google\Cloud\Samples\PubSub;

# [START pubsub_subscribe_avro_records]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Subscribe and pull messages using an AVRO schema.
 *
 * @param string $projectId
 * @param string $subscriptionId
 */
function subscribe_avro_records($projectId, $subscriptionId, $definitionFile)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $subscription = $pubsub->subscription($subscriptionId);
    $definition = file_get_contents($definitionFile);
    $messages = $subscription->pull();

    foreach ($messages as $message) {
        $decodedMessageData = '';
        $encoding = $message->attribute('googclient_schemaencoding');
        switch ($encoding) {
            case 'BINARY':
                $io = new \AvroStringIO($message->data());
                $schema = \AvroSchema::parse($definition);
                $reader = new \AvroIODatumReader($schema);
                $decoder = new \AvroIOBinaryDecoder($io);
                $decodedMessageData = json_encode($reader->read($decoder));
                break;
            case 'JSON':
                $decodedMessageData = $message->data();
                break;
        }

        printf('Received a %d-encoded message %s', $encoding, $decodedMessageData);
    }
}
# [END pubsub_subscribe_avro_records]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
