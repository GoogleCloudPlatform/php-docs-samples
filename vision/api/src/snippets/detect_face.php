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
if (isset($result->info()['faceAnnotations'])) {
    foreach ($result->info()['faceAnnotations'] as $annotation) {
        print("FACE\n");
        if (isset($annotation['boundingPoly'])) {
            print("  BOUNDING POLY\n");
            foreach ($annotation['boundingPoly']['vertices'] as $vertex) {
                $x = isset($vertex['x']) ? $vertex['x'] : '';
                $y = isset($vertex['y']) ? $vertex['y'] : '';
                print("    x:$x\ty:$y\n");
            }
        }
        if (isset($annotation['landmarks'])) {
            print("  LANDMARKS\n");
            foreach ($annotation['landmarks'] as $landmark) {
                $pos = $landmark['position'];
                print("    $landmark[type]:\tx:$pos[x]\ty:$pos[y]\tz:$pos[z]\n");
            }
        }
        $scalar_features = [
            'rollAngle',
            'panAngle',
            'tiltAngle',
            'detectionConfidence',
            'landmarkingConfidence',
            'joyLikelihood',
            'sorrowLikelihood',
            'angerLikelihood',
            'surpriseLikelihood',
            'underExposedLikelihood',
            'blurredLikelihood',
            'headwearLikelihood'
        ];
        foreach ($scalar_features as $feature) {
            if (isset($annotation[$feature])) {
                print("  $feature:\t$annotation[$feature]\n");
            }
        }
    }
}
# [END face_detection]
return $result;
