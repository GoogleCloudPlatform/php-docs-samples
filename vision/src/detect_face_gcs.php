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

// [START vision_face_detection_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

// $path = 'gs://path/to/your/image.jpg'

function detect_face_gcs($path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $response = $imageAnnotator->faceDetection($path);
    $faces = $response->getFaceAnnotations();

    # names of likelihood from google.cloud.vision.enums
    $likelihoodName = ['UNKNOWN', 'VERY_UNLIKELY', 'UNLIKELY',
    'POSSIBLE','LIKELY', 'VERY_LIKELY'];

    printf("%d faces found:" . PHP_EOL, count($faces));
    foreach ($faces as $face) {
        $anger = $face->getAngerLikelihood();
        printf("Anger: %s" . PHP_EOL, $likelihoodName[$anger]);

        $joy = $face->getJoyLikelihood();
        printf("Joy: %s" . PHP_EOL, $likelihoodName[$joy]);

        $surprise = $face->getSurpriseLikelihood();
        printf("Surprise: %s" . PHP_EOL, $likelihoodName[$surprise]);

        # get bounds
        $vertices = $face->getBoundingPoly()->getVertices();
        $bounds = [];
        foreach ($vertices as $vertex) {
            $bounds[] = sprintf('(%d,%d)', $vertex->getX(), $vertex->getY());
        }
        print('Bounds: ' . join(', ',$bounds) . PHP_EOL);
        print(PHP_EOL);
    }

    $imageAnnotator->close();
}
// [END vision_face_detection_gcs]
