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

// [START livestream_create_channel_with_backup_input]
use Google\Cloud\Video\LiveStream\V1\AudioStream;
use Google\Cloud\Video\LiveStream\V1\Channel;
use Google\Cloud\Video\LiveStream\V1\ElementaryStream;
use Google\Cloud\Video\LiveStream\V1\InputAttachment;
use Google\Cloud\Video\LiveStream\V1\Client\LivestreamServiceClient;
use Google\Cloud\Video\LiveStream\V1\CreateChannelRequest;
use Google\Cloud\Video\LiveStream\V1\Manifest;
use Google\Cloud\Video\LiveStream\V1\MuxStream;
use Google\Cloud\Video\LiveStream\V1\SegmentSettings;
use Google\Cloud\Video\LiveStream\V1\VideoStream;
use Google\Protobuf\Duration;

/**
 * Creates a channel with a backup input.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the channel
 * @param string  $channelId          The ID of the channel to be created
 * @param string  $primaryInputId     The ID of the primary input for the channel
 * @param string  $backupInputId      The ID of the backup input for the channel
 * @param string  $outputUri          Uri of the channel output folder in a
 *                                    Cloud Storage bucket. (e.g.
 *                                    "gs://my-bucket/my-output-folder/")
 */
function create_channel_with_backup_input(
    string $callingProjectId,
    string $location,
    string $channelId,
    string $primaryInputId,
    string $backupInputId,
    string $outputUri
): void {
    // Instantiate a client.
    $livestreamClient = new LivestreamServiceClient();

    $parent = $livestreamClient->locationName($callingProjectId, $location);
    $channelName = $livestreamClient->channelName($callingProjectId, $location, $channelId);
    $primaryInputName = $livestreamClient->inputName($callingProjectId, $location, $primaryInputId);
    $backupInputName = $livestreamClient->inputName($callingProjectId, $location, $backupInputId);

    $channel = (new Channel())
        ->setName($channelName)
        ->setInputAttachments([
            new InputAttachment([
                'key' => 'my-primary-input',
                'input' => $primaryInputName,
                'automatic_failover' => new InputAttachment\AutomaticFailover([
                    'input_keys' => ['my-backup-input']
                ])
            ]),
            new InputAttachment([
                'key' => 'my-backup-input',
                'input' => $backupInputName
            ])
        ])
        ->setElementaryStreams([
            new ElementaryStream([
                'key' => 'es_video',
                'video_stream' => new VideoStream([
                    'h264' => new VideoStream\H264CodecSettings([
                        'profile' => 'high',
                        'width_pixels' => 1280,
                        'height_pixels' => 720,
                        'bitrate_bps' => 3000000,
                        'frame_rate' => 30
                    ])
                ]),
            ]),
            new ElementaryStream([
                'key' => 'es_audio',
                'audio_stream' => new AudioStream([
                    'codec' => 'aac',
                    'channel_count' => 2,
                    'bitrate_bps' => 160000
                ])
            ])
        ])
        ->setOutput(new Channel\Output(['uri' => $outputUri]))
        ->setMuxStreams([
            new MuxStream([
                'key' => 'mux_video',
                'elementary_streams' => ['es_video'],
                'segment_settings' => new SegmentSettings([
                    'segment_duration' => new Duration(['seconds' => 2])
                ])
            ]),
            new MuxStream([
                'key' => 'mux_audio',
                'elementary_streams' => ['es_audio'],
                'segment_settings' => new SegmentSettings([
                    'segment_duration' => new Duration(['seconds' => 2])
                ])
            ]),
        ])
        ->setManifests([
            new Manifest([
                'file_name' => 'manifest.m3u8',
                'type' => Manifest\ManifestType::HLS,
                'mux_streams' => ['mux_video', 'mux_audio'],
                'max_segment_count' => 5
            ])
        ]);

    // Run the channel creation request. The response is a long-running operation ID.
    $request = (new CreateChannelRequest())
        ->setParent($parent)
        ->setChannel($channel)
        ->setChannelId($channelId);
    $operationResponse = $livestreamClient->createChannel($request);
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
// [END livestream_create_channel_with_backup_input]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
