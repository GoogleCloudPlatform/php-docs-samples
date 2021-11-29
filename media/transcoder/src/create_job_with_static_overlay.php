<?php

/**
 * Copyright 2021 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/media/transcoder/README.md
 */

namespace Google\Cloud\Samples\Media\Transcoder;

# [START transcoder_create_job_with_static_overlay]
use Google\Cloud\Video\Transcoder\V1\AudioStream;
use Google\Cloud\Video\Transcoder\V1\ElementaryStream;
use Google\Cloud\Video\Transcoder\V1\Job;
use Google\Cloud\Video\Transcoder\V1\JobConfig;
use Google\Cloud\Video\Transcoder\V1\MuxStream;
use Google\Cloud\Video\Transcoder\V1\Overlay;
use Google\Cloud\Video\Transcoder\V1\TranscoderServiceClient;
use Google\Cloud\Video\Transcoder\V1\VideoStream;
use Google\Protobuf\Duration;

/**
 * Creates a job based on a supplied job config that includes a static image overlay.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $location The location of the job.
 * @param string $inputUri Uri of the video in the Cloud Storage bucket.
 * @param string $overlayImageUri Uri of the JPEG image for the overlay in the Cloud Storage bucket. Must be a JPEG.
 * @param string $outputUri Uri of the video output folder in the Cloud Storage bucket.
 */
function create_job_with_static_overlay($projectId, $location, $inputUri, $overlayImageUri, $outputUri)
{
    // Instantiate a client.
    $transcoderServiceClient = new TranscoderServiceClient();

    $formattedParent = $transcoderServiceClient->locationName($projectId, $location);
    $jobConfig =
        (new JobConfig())->setElementaryStreams([
            (new ElementaryStream())
                ->setKey('video-stream0')
                ->setVideoStream(
                    (new VideoStream())
                        ->setH264(
                            (new VideoStream\H264CodecSettings())
                                ->setBitrateBps(550000)
                                ->setFrameRate(60)
                                ->setHeightPixels(360)
                                ->setWidthPixels(640)
                        )
                ),
            (new ElementaryStream())
                ->setKey('audio-stream0')
                ->setAudioStream(
                    (new AudioStream())
                        ->setCodec('aac')
                        ->setBitrateBps(64000)
                )
        ])->setMuxStreams([
            (new MuxStream())
                ->setKey('sd')
                ->setContainer('mp4')
                ->setElementaryStreams(['video-stream0', 'audio-stream0'])
        ])->setOverlays([
            (new Overlay())
                ->setImage(
                    (new Overlay\Image())
                        ->setUri($overlayImageUri)
                        ->setResolution(
                            (new Overlay\NormalizedCoordinate())
                                ->setX(1)
                                ->setY(0.5)
                        )
                        ->setAlpha(1)
                )
                ->setAnimations([
                    (new Overlay\Animation())
                        ->setAnimationStatic(
                            (new Overlay\AnimationStatic())
                                ->setXy(
                                    (new Overlay\NormalizedCoordinate())
                                        ->setY(0)
                                        ->setX(0)
                                )
                                ->setStartTimeOffset(
                                    (new Duration())
                                        ->setSeconds(0)
                                )
                        ),
                    (new Overlay\Animation())
                        ->setAnimationEnd(
                            (new Overlay\AnimationEnd())
                                ->setStartTimeOffset(
                                    (new Duration())
                                        ->setSeconds(10)
                                )
                        )
                ])
        ]);

    $job = (new Job())
        ->setInputUri($inputUri)
        ->setOutputUri($outputUri)
        ->setConfig($jobConfig);

    $response = $transcoderServiceClient->createJob($formattedParent, $job);

    // Print job name.
    printf('Job: %s' . PHP_EOL, $response->getName());
}
# [END transcoder_create_job_with_static_overlay]

require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
