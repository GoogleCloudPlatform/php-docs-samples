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

// [START vision_safe_search_detection]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

// $path = 'path/to/your/image.jpg'

function detect_safe_search($path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $image = file_get_contents($path);
    $response = $imageAnnotator->safeSearchDetection($image);
    $safe = $response->getSafeSearchAnnotation();

    $adult = $safe->getAdult();
    $medical = $safe->getMedical();
    $spoof = $safe->getSpoof();
    $violence = $safe->getViolence();
    $racy = $safe->getRacy();
    
    # names of likelihood from google.cloud.vision.enums
    $likelihoodName = ['UNKNOWN', 'VERY_UNLIKELY', 'UNLIKELY',
    'POSSIBLE','LIKELY', 'VERY_LIKELY'];

    printf("Adult: %s" . PHP_EOL, $likelihoodName[$adult]);
    printf("Medical: %s" . PHP_EOL, $likelihoodName[$medical]);
    printf("Spoof: %s" . PHP_EOL, $likelihoodName[$spoof]);
    printf("Violence: %s" . PHP_EOL, $likelihoodName[$violence]);
    printf("Racy: %s" . PHP_EOL, $likelihoodName[$racy]);

    $imageAnnotator->close();
}
// [END vision_safe_search_detection]
