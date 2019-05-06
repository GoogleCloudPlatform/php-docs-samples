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

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# [START video_quickstart]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

# Instantiate a client.
$video = new VideoIntelligenceServiceClient();

# Execute a request.
$options = [
    'inputUri' => 'gs://cloud-samples-data/video/cat.mp4',
    'features' => [Feature::LABEL_DETECTION]
];
$operation = $video->annotateVideo($options);

# Wait for the request to complete.
$operation->pollUntilComplete();

# Print the result.
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
                $end->getSeconds() + $end->getNanos()/1000000000.0
            );
            printf('  Confidence: %f' . PHP_EOL, $segment->getConfidence());
        }
    }
} else {
    print_r($operation->getError());
}
# [END video_quickstart]
