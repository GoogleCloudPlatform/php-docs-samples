<?php
/**
 * Copyright 2017 Google Inc.
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

# [START videointelligence_quickstart]
use Google\Cloud\VideoIntelligence\V1beta1\VideoIntelligenceServiceClient;
use Google\Cloud\Videointelligence\V1beta1\Feature;

# Instantiate a client.
$video = new VideoIntelligenceServiceClient();

# Execute a request.
$operation = $video->annotateVideo(
    'gs://demomaker/cat.mp4',
    [Feature::LABEL_DETECTION]
);
# Wait for the request to complete.
$operation->pollUntilComplete();

# Print the result.
if (!$operation->operationSucceeded()) {
    print_r($operation->getError());
    die;
}

$results = $operation->getResult()->getAnnotationResults()[0];
foreach ($results->getLabelAnnotations() as $label) {
    printf($label->getDescription() . PHP_EOL);
    foreach ($label->getLocations() as $location) {
        printf('  %ss to %ss' . PHP_EOL,
            $location->getSegment()->getStartTimeOffset() / 1000000,
            $location->getSegment()->getEndTimeOffset() / 1000000);
    }
}
# [END videointelligence_quickstart]
