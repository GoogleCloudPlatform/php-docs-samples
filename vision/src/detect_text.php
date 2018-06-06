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

// [START vision_text_detection]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

// $path = 'path/to/your/image.jpg';

function detect_text($path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $image = file_get_contents($path);
    $response = $imageAnnotator->textDetection($image);
    $texts = $response->getTextAnnotations();

    printf('%d texts found:' . PHP_EOL, count($texts));
    foreach ($texts as $text) {
        print($text->getDescription() . PHP_EOL);

        # get bounds
        $vertices = $text->getBoundingPoly()->getVertices();
        $bounds = [];
        foreach ($vertices as $vertex) {
            $bounds[] = sprintf('(%d,%d)', $vertex->getX(), $vertex->getY());
        }
        print('Bounds: ' . join(', ',$bounds) . PHP_EOL);
    }

    $imageAnnotator->close();
}
// [END vision_text_detection]
