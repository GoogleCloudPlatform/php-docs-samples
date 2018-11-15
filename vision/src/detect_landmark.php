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

// [START vision_landmark_detection]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\VisionClient;

// $path = 'path/to/your/image.jpg1'

function detect_landmark($path)
{
    $vision = new VisionClient();

    # annotate the image
    $imagePhotoResource = file_get_contents($path);
    $image = $vision->image($imagePhotoResource, ['LANDMARK_DETECTION']);
    $annotations = $vision->annotate($image);
    $landmarks = $annotations->landmarks();

    if ($landmarks) {
        printf('%d landmark found:' . PHP_EOL, count($landmarks));
        foreach ($landmarks as $landmark) {
            print($landmark->info()['description'] . PHP_EOL);
        }
    }  else {
        printf('0 landmark found' . PHP_EOL);
    }


}
// [END vision_landmark_detection]
