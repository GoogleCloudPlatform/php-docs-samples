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
    return print("Usage: php analyze_object_tracking_file.php PATH\n");
}
list($_, $path) = $argv;
$options = isset($argv[2]) ? ['pollingIntervalSeconds' => $argv[2]] : [];

// [START video_analyze_object_tracking]
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
    'features' => [Feature::OBJECT_TRACKING]
]);

# Wait for the request to complete.
$operation->pollUntilComplete($options);

# Print the results.
if ($operation->operationSucceeded()) {
    $results = $operation->getResult()->getAnnotationResults()[0];
    # Process video/segment level label annotations
    $objectEntity = $results->getObjectAnnotations()[0];

    printf('Video object entity: %s' . PHP_EOL, $objectEntity->getEntity()->getEntityId());
    printf('Video object description: %s' . PHP_EOL, $objectEntity->getEntity()->getDescription());

    $start = $objectEntity->getSegment()->getStartTimeOffset();
    $end = $objectEntity->getSegment()->getEndTimeOffset();
    printf('  Segment: %ss to %ss' . PHP_EOL,
        $start->getSeconds() + $start->getNanos()/1000000000.0,
        $end->getSeconds() + $end->getNanos()/1000000000.0);
    printf('  Confidence: %f' . PHP_EOL, $objectEntity->getConfidence());

    foreach ($objectEntity->getFrames() as $objectEntityFrame) {
        $offset = $objectEntityFrame->getTimeOffset();
        $boundingBox = $objectEntityFrame->getNormalizedBoundingBox();
        printf('  Time offset: %ss' . PHP_EOL,
            $offset->getSeconds() + $offset->getNanos()/1000000000.0);
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
// [END video_analyze_object_tracking]
