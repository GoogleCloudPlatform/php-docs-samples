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

// [START livestream_stop_channel]
use Google\Cloud\Video\LiveStream\V1\Client\LivestreamServiceClient;
use Google\Cloud\Video\LiveStream\V1\StopChannelRequest;

/**
 * Stops a channel.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the channel
 * @param string  $channelId          The ID of the channel
 */
function stop_channel(
    string $callingProjectId,
    string $location,
    string $channelId
): void {
    // Instantiate a client.
    $livestreamClient = new LivestreamServiceClient();
    $formattedName = $livestreamClient->channelName($callingProjectId, $location, $channelId);

    // Run the channel stop request. The response is a long-running operation ID.
    $request = (new StopChannelRequest())
        ->setName($formattedName);
    $operationResponse = $livestreamClient->stopChannel($request);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        // Print results
        printf('Stopped channel' . PHP_EOL);
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END livestream_stop_channel]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
