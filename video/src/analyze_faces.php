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

// [START analyze_faces]
use Google\Cloud\VideoIntelligence\V1beta1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1beta1\Feature;

/**
 * Finds faces in the video.
 *
 * @param string $uri The cloud storage object to analyze. Must be formatted
 *                    like gs://bucketname/objectname
 */
function analyze_faces($uri)
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Execute a request.
    $operation = $video->annotateVideo(
        $uri,
        [Feature::FACE_DETECTION]);

    # Wait for the request to complete.
    $operation->pollUntilComplete();

    # Print the result.
    if ($operation->operationSucceeded()) {
        $results = $operation->getResult()->getAnnotationResults()[0];
        foreach ($results->getFaceAnnotations() as $face) {
            foreach ($face->getLocations() as $location) {
                if (!$box = $location->getBoundingBox()) {
                    continue;
                }
                printf('At %ss:' . PHP_EOL,
                    $location->getTimeOffset() / 1000000);
                printf('  left: %s' . PHP_EOL, $box->getLeft());
                printf('  right: %s' . PHP_EOL, $box->getRight());
                printf('  bottom: %s' . PHP_EOL, $box->getBottom());
                printf('  top: %s' . PHP_EOL, $box->getTop());
            }
        }
    } else {
        print_r($operation->getError());
    }
}
// [END analyze_faces]
