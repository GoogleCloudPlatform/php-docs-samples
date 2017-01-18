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

// [START image_property_detection]
use Google\Cloud\Vision\VisionClient;

// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'projectId' => $projectId,
]);
$image = $vision->image(file_get_contents($path), [
    'IMAGE_PROPERTIES'
]);
$result = $vision->annotate($image);
print("Properties:\n");
foreach ($result->imageProperties()->colors() as $color) {
    $rgb = $color['color'];
    printf("red:%s\n", $rgb['red']);
    printf("green:%s\n", $rgb['green']);
    printf("blue:%s\n\n", $rgb['blue']);
}
// [END image_property_detection]
