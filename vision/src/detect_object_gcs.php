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

// [START vision_localize_objects_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\ImageSource;

/**
 * @param string $path GCS path to the image, e.g. "gs://path/to/your/image.jpg"
 */
function detect_object_gcs(string $path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $source = (new ImageSource())
        ->setImageUri($path);
    $image = (new Image())
        ->setSource($source);
    $feature = (new Feature())
        ->setType(Type::OBJECT_LOCALIZATION);
    $request = (new AnnotateImageRequest())
        ->setImage($image)
        ->setFeatures([$feature]);
    $batchRequest = (new BatchAnnotateImagesRequest())
        ->setRequests([$request]);
    $response = $imageAnnotator->batchAnnotateImages($batchRequest)->getResponses()[0];
    $objects = $response->getLocalizedObjectAnnotations();

    foreach ($objects as $object) {
        $name = $object->getName();
        $score = $object->getScore();
        $vertices = $object->getBoundingPoly()->getNormalizedVertices();

        printf('%s (confidence %d)):' . PHP_EOL, $name, $score);
        print('normalized bounding polygon vertices: ');
        foreach ($vertices as $vertex) {
            printf(' (%d, %d)', $vertex->getX(), $vertex->getY());
        }
        print(PHP_EOL);
    }

    $imageAnnotator->close();
}
// [END vision_localize_objects_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
