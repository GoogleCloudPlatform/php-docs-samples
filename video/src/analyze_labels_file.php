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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 2 || count($argv) > 3) {
    return print("Usage: php analyze_labels_file.php PATH\n");
}
list($_, $path) = $argv;
$options = isset($argv[2]) ? ['pollingIntervalSeconds' => $argv[2]] : [];

// [START video_analyze_labels]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

/** Uncomment and populate these variables in your code */
// $path = 'File path to a video file to analyze';
// $options = [];

# Instantiate a client.
$video = new VideoIntelligenceServiceClient();

# Read the local video file
$inputContent = file_get_contents($path);

# Execute a request.
$operation = $video->annotateVideo([
    'inputContent' => $inputContent,
    'features' => [Feature::LABEL_DETECTION]
]);

# Wait for the request to complete.
$operation->pollUntilComplete($options);

# Print the results.
if ($operation->operationSucceeded()) {
    $results = $operation->getResult()->getAnnotationResults()[0];

    # Process video/segment level label annotations
    foreach ($results->getSegmentLabelAnnotations() as $label) {
        printf('Video label description: %s' . PHP_EOL, $label->getEntity()->getDescription());
        foreach ($label->getCategoryEntities() as $categoryEntity) {
            printf('  Category: %s' . PHP_EOL, $categoryEntity->getDescription());
        }
        foreach ($label->getSegments() as $segment) {
            $start = $segment->getSegment()->getStartTimeOffset();
            $end = $segment->getSegment()->getEndTimeOffset();
            printf('  Segment: %ss to %ss' . PHP_EOL,
                $start->getSeconds() + $start->getNanos()/1000000000.0,
                $end->getSeconds() + $end->getNanos()/1000000000.0);
            printf('  Confidence: %f' . PHP_EOL, $segment->getConfidence());
        }
    }
    print(PHP_EOL);

    # Process shot level label annotations
    foreach ($results->getShotLabelAnnotations() as $label) {
        printf('Shot label description: %s' . PHP_EOL, $label->getEntity()->getDescription());
        foreach ($label->getCategoryEntities() as $categoryEntity) {
            printf('  Category: %s' . PHP_EOL, $categoryEntity->getDescription());
        }
        foreach ($label->getSegments() as $shot) {
            $start = $shot->getSegment()->getStartTimeOffset();
            $end = $shot->getSegment()->getEndTimeOffset();
            printf('  Shot: %ss to %ss' . PHP_EOL,
                $start->getSeconds() + $start->getNanos()/1000000000.0,
                $end->getSeconds() + $end->getNanos()/1000000000.0);
            printf('  Confidence: %f' . PHP_EOL, $shot->getConfidence());
        }
    }
    print(PHP_EOL);
} else {
    print_r($operation->getError());
}
// [END video_analyze_labels]
