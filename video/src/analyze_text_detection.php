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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 2 || count($argv) > 3) {
    return print("Usage: php analyze_text_detection.php URI\n");
}
list($_, $uri) = $argv;
$options = isset($argv[2]) ? ['pollingIntervalSeconds' => $argv[2]] : [];

// [START video_analyze_text_detection]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

/** Uncomment and populate these variables in your code */
// $uri = 'The cloud storage object to analyze (gs://your-bucket-name/your-object-name)';
// $options = [];

# Instantiate a client.
$video = new VideoIntelligenceServiceClient();

# Execute a request.
$operation = $video->annotateVideo([
    'inputUri' => $uri,
    'features' => [Feature::TEXT_DETECTION]
]);

# Wait for the request to complete.
$operation->pollUntilComplete($options);

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
                $start->getSeconds() + $start->getNanos()/1000000000.0,
                $end->getSeconds() + $end->getNanos()/1000000000.0);
            printf('  Confidence: %f' . PHP_EOL, $segment->getConfidence());
        }
    }
    print(PHP_EOL);
} else {
    print_r($operation->getError());
}
// [END video_analyze_text_detection]
