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

require_once __DIR__ . '/data/generated/Metadata.php';
require_once __DIR__ . '/data/generated/StateProto.php';

# [START pubsub_publish_proto_messages]
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\V1\Encoding;

use Utilities\StateProto;

/**
 * Publish a message using a protocol buffer schema.
 *
 * Relies on a proto message of the following form:
 * ```
 * syntax = "proto3";
 *
 * package utilities;
 *
 * message StateProto {
 *   string name = 1;
 *   string post_abbr = 2;
 * }
 * ```
 *
 * @param string $projectId
 * @param string $topicId
 * @return void
 */
function publish_proto_messages($projectId, $topicId)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $messageData = new StateProto([
        'name' => 'Alaska',
        'post_abbr' => 'AK',
    ]);

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
        // encode as protobuf binary.
        $encodedMessageData = $messageData->serializeToString();
    } else {
        // encode as JSON.
        $encodedMessageData = $messageData->serializeToJsonString();
    }

    $topic->publish(['data' => $encodedMessageData]);

    printf('Published message with %s encoding', $encoding);
}
# [END pubsub_publish_proto_messages]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
