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

// [START vision_web_detection]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;

// $path = 'path/to/your/image.jpg'

function detect_web($path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $image = file_get_contents($path);
    $response = $imageAnnotator->webDetection($image);
    $web = $response->getWebDetection();

    if ($web->getBestGuessLabels()) {
        foreach ($web->getBestGuessLabels() as $label) {
            printf('Best guess label: %s' . PHP_EOL, $label->getLabel());
        }
        print(PHP_EOL);
    }

    if ($web->getPagesWithMatchingImages()) {
        printf('%d pages with matching images found:' . PHP_EOL, 
            count($web->getPagesWithMatchingImages()));
        foreach ($web->getPagesWithMatchingImages() as $page) {
            printf('URL: %s' . PHP_EOL, $page->getUrl());
        }
        print(PHP_EOL);
    }

    if ($web->getFullMatchingImages()) {
        printf('%d full matching images found:' . PHP_EOL, 
            count($web->getFullMatchingImages()));
        foreach ($web->getFullMatchingImages() as $fullMatchingImage) {
            printf('URL: %s' . PHP_EOL, $fullMatchingImage->getUrl());
        }
        print(PHP_EOL);
    }

    if ($web->getPartialMatchingImages()) {
        printf('%d partial matching images found:' . PHP_EOL, 
            count($web->getPartialMatchingImages()));
        foreach ($web->getPartialMatchingImages() as $partialMatchingImage) {
            printf('URL: %s' . PHP_EOL, $partialMatchingImage->getUrl());
        }
        print(PHP_EOL);
    }

    if ($web->getVisuallySimilarImages()) {
        printf('%d visually similar images found:' . PHP_EOL, 
            count($web->getVisuallySimilarImages()));
        foreach ($web->getVisuallySimilarImages() as $visuallySimilarImage) {
            printf('URL: %s' . PHP_EOL, $visuallySimilarImage->getUrl());
        }
        print(PHP_EOL);
    }

    if ($web->getWebEntities()) {
        printf('%d web entities found:' . PHP_EOL, 
            count($web->getWebEntities()));
        foreach ($web->getWebEntities() as $entity) {
            printf('Description: %s' . PHP_EOL, $entity->getDescription());
            printf('Score: %f' . PHP_EOL, $entity->getScore());
            print(PHP_EOL);
        }
    }
}
// [END vision_web_detection]
