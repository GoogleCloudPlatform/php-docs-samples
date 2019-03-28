<?php

/**
 * Copyright 2019 Google Inc.
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

// [START video_analyze_object_tracking]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

/**
 * Finds labels in the video.
 *
 * @param string $path File path to a video file to analyze.
 * @param array $options optional Array of options to pass to
 *                       OperationResponse::pollUntilComplete. This is useful
 *                       for increasing the "pollingIntervalSeconds" option.
 */
function analyze_object_tracking_file($path, array $options = [])
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Read the local video file
    $inputContent = file_get_contents($path);

    # Execute a request.
    $operation = $video->annotateVideo([
        'inputContent' => $inputContent,
        'features' => [Feature::OBJECT_TRACKING]
    ]);

    # Wait for the request to complete.
    $operation->pollUntilComplete($options);

    # Print the results.
    if ($operation->operationSucceeded()) {
        $results = $operation->getResult()->getAnnotationResults()[0];
        # Process video/segment level label annotations
        $objectAnnotation = $results->getObjectAnnotations()[0];

        printf('Video object entity: %s' . PHP_EOL, $objectAnnotation->getEntity()->getEntityId());
        printf('Video object description: %s' . PHP_EOL, $objectAnnotation->getEntity()->getDescription());

        $startTimeOffset = $objectAnnotation->getSegment()->getStartTimeOffset();
        $startSeconds = $startTimeOffset->getSeconds();
        $startNanoseconds = floatval($startTimeOffset->getNanos())/1000000000.00;
        $startTime = $startSeconds + $startNanoseconds;
        $endTimeOffset = $objectAnnotation->getSegment()->getEndTimeOffset();
        $endSeconds = $endTimeOffset->getSeconds();
        $endNanoseconds = floatval($endTimeOffset->getNanos())/1000000000.00;
        $endTime = $endSeconds + $endNanoseconds;
        printf('  Segment: %ss to %ss' . PHP_EOL, $startTime, $endTime);
        printf('  Confidence: %f' . PHP_EOL, $objectAnnotation->getConfidence());

        foreach ($objectAnnotation->getFrames() as $objectAnnotationFrame) {
            $startSeconds = $objectAnnotationFrame->getTimeOffset()->getSeconds();
            $startNanoseconds = $objectAnnotationFrame->getTimeOffset()->getNanos();
            $timeOffSet = $startSeconds + $startNanoseconds/1000000000.00;
            printf('  Time offset: %ss' . PHP_EOL, $timeOffSet);
            $boundingBox = $objectAnnotationFrame->getNormalizedBoundingBox();
            printf('  Bounding box position:' . PHP_EOL);
            printf('   Left: %s', $boundingBox->getLeft());
            printf('   Top: %s', $boundingBox->getTop());
            printf('   Right: %s', $boundingBox->getRight());
            printf('   Bottom: %s', $boundingBox->getBottom());
        }
        print(PHP_EOL);
    } else {
        print_r($operation->getError());
    }
}
// [END video_analyze_object_tracking]
