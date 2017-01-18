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

# [START face_detection]
# [START get_vision_service]
use Google\Cloud\Vision\VisionClient;

// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'projectId' => $projectId,
]);
# [END get_vision_service]
# [START detect_face]
$image = $vision->image(file_get_contents($path), ['FACE_DETECTION']);
$result = $vision->annotate($image);
# [END detect_face]
print("Faces:\n");
foreach ((array) $result->faces() as $face) {
    printf("Anger: %s\n", $face->isAngry() ? 'yes' : 'no');
    printf("Joy: %s\n", $face->isJoyful() ? 'yes' : 'no');
    printf("Surprise: %s\n\n", $face->isSurprised() ? 'yes' : 'no');
}
# [END face_detection]
return $result;
