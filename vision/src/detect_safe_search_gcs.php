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

// [START vision_safe_search_detection_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\VisionClient;

// $path = 'gs://path/to/your/image.jpg'

function detect_safe_search_gcs($path)
{
    $vision = new VisionClient();

    # annotate the image
    $image = $vision->image($path, ['SAFE_SEARCH_DETECTION']);
    $annotations = $vision->annotate($image);
    $safe = $annotations->safeSearch();

    if($safe) {

        # names of likelihood from google.cloud.vision.enums
        $adult = $safe->adult();
        $medical = $safe->medical();
        $spoof = $safe->spoof();
        $violence = $safe->violence();
        $racy = $safe->racy();

        printf("Adult: %s" . PHP_EOL, $adult );
        printf("Medical: %s" . PHP_EOL, $medical);
        printf("Spoof: %s" . PHP_EOL, $spoof);
        printf("Violence: %s" . PHP_EOL, $violence);
        printf("Racy: %s" . PHP_EOL, $racy);
    } else {
        print('No Results.' . PHP_EOL);
    }
}
// [END vision_safe_search_detection_gcs]