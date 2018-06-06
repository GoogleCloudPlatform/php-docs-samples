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

// [START vision_landmark_detection_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

// $path = 'gs://path/to/your/image.jpg'

function detect_landmark_gcs($path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $response = $imageAnnotator->landmarkDetection($path);
    $landmarks = $response->getLandmarkAnnotations();

    printf('%d landmark found:' . PHP_EOL, count($landmarks));
    foreach ($landmarks as $landmark) {
        print($landmark->getDescription() . PHP_EOL);
    }

    $imageAnnotator->close();
}
// [END vision_landmark_detection_gcs]
