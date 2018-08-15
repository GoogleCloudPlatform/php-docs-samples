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

// [START vision_crop_hint_detection]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

// $path = 'path/to/your/image.jpg'

function detect_crop_hints($path)
{
    $imageAnnotator = new ImageAnnotatorClient();
    
    # annotate the image
    $image = file_get_contents($path);
    $response = $imageAnnotator->cropHintsDetection($image);
    $annotations = $response->getCropHintsAnnotation();

    # print the crop hints from the annotation
    if ($annotations) {
        print("Crop hints:" . PHP_EOL);
        foreach ($annotations->getCropHints() as $hint) {
            # get bounds
            $vertices = $hint->getBoundingPoly()->getVertices();
            $bounds = [];
            foreach ($vertices as $vertex) {
                $bounds[] = sprintf('(%d,%d)', $vertex->getX(),
                    $vertex->getY());
            }
            print('Bounds: ' . join(', ',$bounds) . PHP_EOL);
        }
    } else {
        print('No crop hints' . PHP_EOL);
    }
    
    $imageAnnotator->close();
}
// [END vision_crop_hint_detection]
