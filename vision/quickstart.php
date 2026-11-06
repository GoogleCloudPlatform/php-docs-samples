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

# [START vision_quickstart]
# includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# imports the Google Cloud client library
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\Image;

# instantiates a client
$imageAnnotator = new ImageAnnotatorClient();

# the name of the image file to annotate
$fileName = 'test/data/wakeupcat.jpg';

# prepare the image to be annotated
$image = (new Image())
    ->setContent(file_get_contents($fileName));
$feature = (new Feature())
    ->setType(Type::LABEL_DETECTION);
$request = (new AnnotateImageRequest())
    ->setImage($image)
    ->setFeatures([$feature]);
$batchRequest = (new BatchAnnotateImagesRequest())
    ->setRequests([$request]);

# performs label detection on the image file
$response = $imageAnnotator->batchAnnotateImages($batchRequest)->getResponses()[0];
$labels = $response->getLabelAnnotations();

if ($labels) {
    echo('Labels:' . PHP_EOL);
    foreach ($labels as $label) {
        echo($label->getDescription() . PHP_EOL);
    }
} else {
    echo('No label found' . PHP_EOL);
}
# [END vision_quickstart]
return $labels;
