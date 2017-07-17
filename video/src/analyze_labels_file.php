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

// [START analyze_labels_file]
use Google\Cloud\VideoIntelligence\V1beta1\VideoIntelligenceServiceClient;
use Google\Cloud\Videointelligence\V1beta1\Feature;

/**
 * Finds labels in the video.
 *
 * @param string $path File path to a video file to analyze.
 */
function analyze_labels_file($path)
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Read the local video file and convert it to base64
    $inputContent = base64_encode(
        file_get_contents($path)
    );

    # Execute a request.
    $operation = $video->annotateVideo(
        '',
        [Feature::LABEL_DETECTION],
        ['inputContent' => $inputContent]);

    # Wait for the request to complete.
    $operation->pollUntilComplete();

    # Print the result.
    if ($operation->operationSucceeded()) {
        $results = $operation->getResult()->getAnnotationResults()[0];
        foreach ($results->getLabelAnnotations() as $label) {
            printf($label->getDescription() . PHP_EOL);
            foreach ($label->getLocations() as $location) {
                printf('  %ss to %ss' . PHP_EOL,
                    $location->getSegment()->getStartTimeOffset() / 1000000,
                    $location->getSegment()->getEndTimeOffset() / 1000000);
            }
        }
    } else {
        print_r($operation->getError());
    }
}
// [END analyze_labels_file]
