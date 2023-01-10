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

// [START livestream_create_channel_event]
use Google\Cloud\Video\LiveStream\V1\LivestreamServiceClient;
use Google\Cloud\Video\LiveStream\V1\Event;
use Google\Protobuf\Duration;

/**
 * Creates a channel event. This particular sample inserts an ad break marker.
 * Other event types are supported.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the channel
 * @param string  $channelId          The ID of the channel
 * @param string  $eventId            The ID of the channel event
 */
function create_channel_event(
    string $callingProjectId,
    string $location,
    string $channelId,
    string $eventId
): void {
    // Instantiate a client.
    $livestreamClient = new LivestreamServiceClient();

    $parent = $livestreamClient->channelName($callingProjectId, $location, $channelId);

    $eventAdBreak = (new Event\AdBreakTask())
        ->setDuration(new Duration(['seconds' => 30]));
    $event = (new Event())
        ->setAdBreak($eventAdBreak)
        ->setExecuteNow(true);

    // Run the channel event creation request.
    $response = $livestreamClient->createEvent($parent, $event, $eventId);
    // Print results.
    printf('Channel event: %s' . PHP_EOL, $response->getName());
}
// [END livestream_create_channel_event]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
