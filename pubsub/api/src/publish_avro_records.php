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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/pubsub/api/README.md
 */
namespace Google\Cloud\Samples\PubSub;

# [START pubsub_publish_avro_records]
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\V1\Encoding;

use AvroStringIO;
use AvroSchema;
use AvroIODatumWriter;
use AvroDataIOWriter;

/**
 * Publish a message using an AVRO schema.
 *
 * This sample uses `wikimedia/avro` for AVRO encoding.
 *
 * @param string $projectId
 * @param string $topicId
 * @param string $definitionFile
 * @return void
 */
function publish_avro_records($projectId, $topicId, $definitionFile)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $definition = file_get_contents($definitionFile);

    $messageData = [
        'name' => 'Alaska',
        'post_abbr' => 'AK',
    ];

    $topic = $pubsub->topic($topicId);

    // get the encoding type.
    $topicInfo = $topic->info();
    $encoding = '';
    if (isset($topicInfo['schemaSettings']['encoding'])) {
        $encoding = $topicInfo['schemaSettings']['encoding'];
    }

    // if encoding is not set, we can't continue.
    if ($encoding === '') {
        printf('Topic %s does not have schema enabled', $topicId);
        return;
    }

    // If you are using gRPC, encoding may be an integer corresponding to an
    // enum value on Google\Cloud\PubSub\V1\Encoding.
    if (!is_string($encoding)) {
        $encoding = Encoding::name($encoding);
    }

    $encodedMessageData = '';
    if ($encoding == 'BINARY') {
        // encode as AVRO binary.
        $io = new AvroStringIO();
        $schema = AvroSchema::parse($definition);
        $writer = new AvroIODatumWriter($schema);
        $dataWriter = new AvroDataIOWriter($io, $writer, $schema);

        $dataWriter->append($messageData);

        $dataWriter->close();

        // AVRO binary data must be base64-encoded.
        $encodedMessageData = base64_encode($io->string());
    } else {
        // encode as JSON.
        $encodedMessageData = json_encode($messageData);
    }

    $topic->publish(['data' => $encodedMessageData]);

    printf('Published message with %s encoding', $encoding);
}
# [END pubsub_publish_avro_records]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
