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

namespace Google\Cloud\Samples\Vision;

// [START vision_crop_hint_detection]
use Google\Cloud\Vision\VisionClient;

// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'projectId' => $projectId,
]);
$image = $vision->image(file_get_contents($path), ['CROP_HINTS']);
$result = $vision->annotate($image);
print("Crop Hints:\n");
foreach ((array) $result->cropHints() as $hint) {
    $boundingPoly = $hint->boundingPoly();
    $vertices = $boundingPoly['vertices'];
    foreach ((array) $vertices as $vertice) {
        if (!isset($vertice['x'])) $vertice['x'] = 0;
        if (!isset($vertice['y'])) $vertice['y'] = 0;
        print('X: ' . $vertice['x'] . ' Y: ' . $vertice['y'] . PHP_EOL);
    }
}
// [END vision_crop_hint_detection]
