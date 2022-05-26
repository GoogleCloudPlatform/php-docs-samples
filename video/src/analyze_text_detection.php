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

// [START video_detect_text_gcs]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

/**
 * @param string $uri The cloud storage object to analyze (gs://your-bucket-name/your-object-name)
 * @param int $pollingIntervalSeconds
 */
function analyze_text_detection(string $uri, int $pollingIntervalSeconds = 0)
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Execute a request.
    $features = [Feature::TEXT_DETECTION];
    $operation = $video->annotateVideo([
        'inputUri' => $uri,
        'features' => $features,
    ]);

    # Wait for the request to complete.
    $operation->pollUntilComplete([
        'pollingIntervalSeconds' => $pollingIntervalSeconds
    ]);

    # Print the results.
    if ($operation->operationSucceeded()) {
        $results = $operation->getResult()->getAnnotationResults()[0];

        # Process video/segment level label annotations
        foreach ($results->getTextAnnotations() as $text) {
            printf('Video text description: %s' . PHP_EOL, $text->getText());
            foreach ($text->getSegments() as $segment) {
                $start = $segment->getSegment()->getStartTimeOffset();
                $end = $segment->getSegment()->getEndTimeOffset();
                printf('  Segment: %ss to %ss' . PHP_EOL,
                    $start->getSeconds() + $start->getNanos() / 1000000000.0,
                    $end->getSeconds() + $end->getNanos() / 1000000000.0);
                printf('  Confidence: %f' . PHP_EOL, $segment->getConfidence());
            }
        }
        print(PHP_EOL);
    } else {
        print_r($operation->getError());
    }
}
// [END video_detect_text_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
