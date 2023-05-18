<?php

/**
 * Copyright 2022 Google LLC.
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
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/media/livestream/README.md
 */

namespace Google\Cloud\Samples\Media\LiveStream;

// [START livestream_update_channel]
use Google\Cloud\Video\LiveStream\V1\LivestreamServiceClient;
use Google\Cloud\Video\LiveStream\V1\Channel;
use Google\Cloud\Video\LiveStream\V1\InputAttachment;
use Google\Protobuf\FieldMask;

/**
 * Updates an existing channel with a different input.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the channel and the input
 * @param string  $channelId          The ID of the channel to be updated
 * @param string  $inputId            The ID of the existing input
 */
function update_channel(
    string $callingProjectId,
    string $location,
    string $channelId,
    string $inputId
): void {
    // Instantiate a client.
    $livestreamClient = new LivestreamServiceClient();

    $channelName = $livestreamClient->channelName($callingProjectId, $location, $channelId);
    $inputName = $livestreamClient->inputName($callingProjectId, $location, $inputId);

    $inputAttachment = (new InputAttachment())
        ->setKey('updated-input')
        ->setInput($inputName);
    $channel = (new Channel())
        ->setName($channelName)
        ->setInputAttachments([$inputAttachment]);

    $updateMask = new FieldMask([
        'paths' => ['input_attachments']
    ]);

    // Run the channel update request. The response is a long-running operation ID.
    $operationResponse = $livestreamClient->updateChannel($channel, ['updateMask' => $updateMask]);

    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        // Print results
        printf('Updated channel: %s' . PHP_EOL, $result->getName());
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END livestream_update_channel]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
