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

use Google\Cloud\Vision\VisionClient;

// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

function detect_crop_hints($projectId, $path)
{
    $vision = new VisionClient([
        'projectId' => $projectId,
    ]);

    # Annotate the image
    $image = $vision->image(file_get_contents($path), ['CROP_HINTS']);
    $annotation = $vision->annotate($image);

    # Print the crop hints from the annotation
    printf("Crop Hints:\n");
    foreach ((array) $annotation->cropHints() as $hint) {
        $boundingPoly = $hint->boundingPoly();
        $vertices = $boundingPoly['vertices'];
        foreach ((array) $vertices as $vertice) {
            if (!isset($vertice['x'])) {
                $vertice['x'] = 0;
            }
            if (!isset($vertice['y'])) {
                $vertice['y'] = 0;
            }
            printf('X: %s Y: %s' . PHP_EOL, $vertice['x'], $vertice['y']);
        }
    }
}
// [END vision_crop_hint_detection]
