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

// [START vision_label_detection_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

/**
 * @param string $path GCS path to the image, e.g. "gs://path/to/your/image.jpg"
 */
function detect_label_gcs(string $path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $response = $imageAnnotator->labelDetection($path);
    $labels = $response->getLabelAnnotations();

    if ($labels->count()) {
        print('Labels:' . PHP_EOL);
        foreach ($labels as $label) {
            print($label->getDescription() . PHP_EOL);
        }
    } else {
        print('No label found' . PHP_EOL);
    }

    $imageAnnotator->close();
}
// [END vision_label_detection_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
