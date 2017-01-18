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


// [START landmark_detection]
use Google\Cloud\Vision\VisionClient;

// [START get_vision_service]
// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'projectId' => $projectId,
]);
// [END get_vision_service]
// [START construct_request]
$image = $vision->image(file_get_contents($path), ['LANDMARK_DETECTION']);
$result = $vision->annotate($image);
// [END construct_request]
print("Landmarks:\n");
foreach ((array) $result->landmarks() as $landmark) {
    print($landmark->description() . PHP_EOL);
}
// [END landmark_detection]
