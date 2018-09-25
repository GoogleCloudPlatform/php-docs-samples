<?php

/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Samples\Video;

// [START video_analyze_shots]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

/**
 * Finds shot changes in the video.
 *
 * @param string $uri The cloud storage object to analyze. Must be formatted
 *                    like gs://bucketname/objectname.
 * @param array $options optional Array of options to pass to
 *                       OperationResponse::pollUntilComplete. This is useful
 *                       for increasing the "pollingIntervalSeconds" option.
 */
function analyze_shots($uri, array $options = [])
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Execute a request.
    $operation = $video->annotateVideo([
        'inputUri' => $uri,
        'features' => [Feature::SHOT_CHANGE_DETECTION]
    ]);

    # Wait for the request to complete.
    $operation->pollUntilComplete($options);

    # Print the result.
    if ($operation->operationSucceeded()) {
        $results = $operation->getResult()->getAnnotationResults()[0];
        foreach ($results->getShotAnnotations() as $shot) {
            $startTimeOffset = $shot->getStartTimeOffset();
            $startSeconds = $startTimeOffset->getSeconds();
            $startNanoseconds = floatval($startTimeOffset->getNanos())/1000000000.00;
            $startTime = $startSeconds + $startNanoseconds;
            $endTimeOffset = $shot->getEndTimeOffset();
            $endSeconds = $endTimeOffset->getSeconds();
            $endNanoseconds = floatval($endTimeOffset->getNanos())/1000000000.00;
            $endTime = $endSeconds + $endNanoseconds;
            printf('Shot: %ss to %ss' . PHP_EOL, $startTime, $endTime);
        }
    } else {
        print_r($operation->getError());
    }
}
// [END video_analyze_shots]
