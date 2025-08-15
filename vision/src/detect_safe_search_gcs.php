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

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;

/**
 * @param string $path GCS path to the image, e.g. "gs://path/to/your/image.jpg"
 */
function detect_safe_search_gcs(string $path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $response = $imageAnnotator->safeSearchDetection($path);
    $safe = $response->getSafeSearchAnnotation();

    if ($safe) {
        $adult = $safe->getAdult();
        $medical = $safe->getMedical();
        $spoof = $safe->getSpoof();
        $violence = $safe->getViolence();
        $racy = $safe->getRacy();

        # names of likelihood from google.cloud.vision.enums
        $likelihoodName = ['UNKNOWN', 'VERY_UNLIKELY', 'UNLIKELY',
        'POSSIBLE', 'LIKELY', 'VERY_LIKELY'];

        printf('Adult: %s' . PHP_EOL, $likelihoodName[$adult]);
        printf('Medical: %s' . PHP_EOL, $likelihoodName[$medical]);
        printf('Spoof: %s' . PHP_EOL, $likelihoodName[$spoof]);
        printf('Violence: %s' . PHP_EOL, $likelihoodName[$violence]);
        printf('Racy: %s' . PHP_EOL, $likelihoodName[$racy]);
    } else {
        print('No Results.' . PHP_EOL);
    }

    $imageAnnotator->close();
}
// [END vision_safe_search_detection_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
