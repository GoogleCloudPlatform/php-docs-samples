<?php
/**
 * Copyright 2018 Google Inc.
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

// [START vision_web_detection_include_geo]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\ImageContext;
use Google\Cloud\Vision\V1\WebDetectionParams;

/**
 * Detect web entities on an image and include the image's geo metadata
 * to improve the quality of the detection.
 *
 * @param string $path Path to the image, e.g. "path/to/your/image.jpg"
 */
function detect_web_with_geo_metadata(string $path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # enable include geo results
    $params = new WebDetectionParams();
    $params->setIncludeGeoResults(true);
    $imageContext = new ImageContext();
    $imageContext-> setWebDetectionParams($params);

    # annotate the image
    $image = file_get_contents($path);
    $response = $imageAnnotator->webDetection($image, ['imageContext' => $imageContext]);
    $web = $response->getWebDetection();

    if ($web && $web->getWebEntities()->count()) {
        printf('%d web entities found:' . PHP_EOL,
            count($web->getWebEntities()));
        foreach ($web->getWebEntities() as $entity) {
            printf('Description: %s ' . PHP_EOL, $entity->getDescription());
            printf('Score: %f' . PHP_EOL, $entity->getScore());
            print(PHP_EOL);
        }
    }

    $imageAnnotator->close();
}
// [END vision_web_detection_include_geo]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
