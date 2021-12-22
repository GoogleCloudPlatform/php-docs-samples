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

# [START transcoder_create_job_with_concatenated_inputs]
use Google\Cloud\Video\Transcoder\V1\AudioStream;
use Google\Cloud\Video\Transcoder\V1\EditAtom;
use Google\Cloud\Video\Transcoder\V1\ElementaryStream;
use Google\Cloud\Video\Transcoder\V1\Input;
use Google\Cloud\Video\Transcoder\V1\Job;
use Google\Cloud\Video\Transcoder\V1\JobConfig;
use Google\Cloud\Video\Transcoder\V1\MuxStream;
use Google\Cloud\Video\Transcoder\V1\TranscoderServiceClient;
use Google\Cloud\Video\Transcoder\V1\VideoStream;
use Google\Protobuf\Duration;

/**
 * Creates a job based on a supplied job config that concatenates two input videos.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $location The location of the job.
 * @param string $input1Uri Uri of the first video in the Cloud Storage bucket.
 * @param float  $startTimeInput1 Start time, in fractional seconds, relative to the first input video timeline.
 * @param float  $endTimeInput1 End time, in fractional seconds, relative to the first input video timeline.
 * @param string $input2Uri Uri of the second video in the Cloud Storage bucket.
 * @param float  $startTimeInput2 Start time, in fractional seconds, relative to the second input video timeline.
 * @param float  $endTimeInput2 End time, in fractional seconds, relative to the second input video timeline.
 * @param string $outputUri Uri of the video output folder in the Cloud Storage bucket.
 */
function create_job_with_concatenated_inputs($projectId, $location, $input1Uri, $startTimeInput1, $endTimeInput1, $input2Uri, $startTimeInput2, $endTimeInput2, $outputUri)
{
    $startTimeInput1Sec = (int) floor(abs($startTimeInput1));
    $startTimeInput1Nanos = (int) (1000000000 * bcsub(abs($startTimeInput1), floor(abs($startTimeInput1)), 4));
    $endTimeInput1Sec = (int) floor(abs($endTimeInput1));
    $endTimeInput1Nanos = (int) (1000000000 * bcsub(abs($endTimeInput1), floor(abs($endTimeInput1)), 4));

    $startTimeInput2Sec = (int) floor(abs($startTimeInput2));
    $startTimeInput2Nanos = (int) (1000000000 * bcsub(abs($startTimeInput2), floor(abs($startTimeInput2)), 4));
    $endTimeInput2Sec = (int) floor(abs($endTimeInput2));
    $endTimeInput2Nanos = (int) (1000000000 * bcsub(abs($endTimeInput2), floor(abs($endTimeInput2)), 4));

    // Instantiate a client.
    $transcoderServiceClient = new TranscoderServiceClient();

    $formattedParent = $transcoderServiceClient->locationName($projectId, $location);
    $jobConfig =
        (new JobConfig())->setInputs([
            (new Input())
                ->setKey('input1')
                ->setUri($input1Uri),
            (new Input())
                ->setKey('input2')
                ->setUri($input2Uri)
        ])->setEditList([
            (new EditAtom())
                ->setKey('atom1')
                ->setInputs(['input1'])
                ->setStartTimeOffset(new Duration(['seconds' => $startTimeInput1Sec, 'nanos' => $startTimeInput1Nanos]))
                ->setEndTimeOffset(new Duration(['seconds' => $endTimeInput1Sec, 'nanos' => $endTimeInput1Nanos])),
            (new EditAtom())
                ->setKey('atom2')
                ->setInputs(['input2'])
                ->setStartTimeOffset(new Duration(['seconds' => $startTimeInput2Sec, 'nanos' => $startTimeInput2Nanos]))
                ->setEndTimeOffset(new Duration(['seconds' => $endTimeInput2Sec, 'nanos' => $endTimeInput2Nanos])),
        ])->setElementaryStreams([
            (new ElementaryStream())
                ->setKey('video-stream0')
                ->setVideoStream(
                    (new VideoStream())->setH264(
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
        ]);

    $job = (new Job())
        ->setOutputUri($outputUri)
        ->setConfig($jobConfig);

    $response = $transcoderServiceClient->createJob($formattedParent, $job);

    // Print job name.
    printf('Job: %s' . PHP_EOL, $response->getName());
}
# [END transcoder_create_job_with_concatenated_inputs]

require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
