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

// [START livestream_create_channel]
use Google\Cloud\Video\LiveStream\V1\LivestreamServiceClient;
use Google\Cloud\Video\LiveStream\V1\AudioStream;
use Google\Cloud\Video\LiveStream\V1\Channel;
use Google\Cloud\Video\LiveStream\V1\ElementaryStream;
use Google\Cloud\Video\LiveStream\V1\InputAttachment;
use Google\Cloud\Video\LiveStream\V1\Manifest;
use Google\Cloud\Video\LiveStream\V1\MuxStream;
use Google\Cloud\Video\LiveStream\V1\SegmentSettings;
use Google\Cloud\Video\LiveStream\V1\VideoStream;
use Google\Protobuf\Duration;

/**
 * Creates a channel.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the channel
 * @param string  $channelId          The ID of the channel to be created
 * @param string  $inputId            The ID of the input for the channel
 * @param string  $outputUri          Uri of the channel output folder in a
 *                                    Cloud Storage bucket. (e.g.
 *                                    "gs://my-bucket/my-output-folder/")
 */
function create_channel(
    string $callingProjectId,
    string $location,
    string $channelId,
    string $inputId,
    string $outputUri
): void {
    // Instantiate a client.
    $livestreamClient = new LivestreamServiceClient();

    $parent = $livestreamClient->locationName($callingProjectId, $location);
    $channelName = $livestreamClient->channelName($callingProjectId, $location, $channelId);
    $inputName = $livestreamClient->inputName($callingProjectId, $location, $inputId);

    $channel =
        (new Channel())
            ->setName($channelName)
            ->setInputAttachments([
                (new InputAttachment())
                    ->setKey('my-input')
                    ->setInput($inputName)
            ])
            ->setElementaryStreams([
                (new ElementaryStream())
                    ->setKey('es_video')
                    ->setVideoStream(
                        (new VideoStream())
                            ->setH264(
                                (new VideoStream\H264CodecSettings())
                                    ->setProfile('high')
                                    ->setWidthPixels(1280)
                                    ->setHeightPixels(720)
                                    ->setBitrateBps(3000000)
                                    ->setFrameRate(30)
                            )
                    ),
                (new ElementaryStream())
                    ->setKey('es_audio')
                    ->setAudioStream(
                        (new AudioStream())
                            ->setCodec('aac')
                            ->setChannelCount(2)
                            ->setBitrateBps(160000)
                    )
            ])
            ->setOutput(
                (new Channel\Output())
                                ->setUri($outputUri)
            )
            ->setMuxStreams([
            (new MuxStream())
                ->setKey('mux_video')
                ->setElementaryStreams(['es_video'])
                ->setSegmentSettings(
                    (new SegmentSettings())
                        ->setSegmentDuration(
                            (new Duration())
                                ->setSeconds(2)
                        )
                ),
            (new MuxStream())
                ->setKey('mux_audio')
                ->setElementaryStreams(['es_audio'])
                ->setSegmentSettings(
                    (new SegmentSettings())
                        ->setSegmentDuration(
                            (new Duration())
                                ->setSeconds(2)
                        )
                )
            ])
            ->setManifests([
                (new Manifest())
                    ->setFileName('manifest.m3u8')
                    ->setType(1)
                    ->setMuxStreams(['mux_video', 'mux_audio'])
                    ->setMaxSegmentCount(5)
            ]);

    // Run the channel creation request. The response is a long-running operation ID.
    $operationResponse = $livestreamClient->createChannel($parent, $channel, $channelId);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        // Print results
        printf('Channel: %s' . PHP_EOL, $result->getName());
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
}
// [END livestream_create_channel]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
