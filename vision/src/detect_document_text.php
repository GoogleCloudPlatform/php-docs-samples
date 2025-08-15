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

// [START vision_fulltext_detection]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;

/**
 * @param string $path Path to the image, e.g. "path/to/your/image.jpg"
 */
function detect_document_text(string $path)
{
    $imageAnnotator = new ImageAnnotatorClient();

    # annotate the image
    $image = file_get_contents($path);
    $response = $imageAnnotator->documentTextDetection($image);
    $annotation = $response->getFullTextAnnotation();

    # print out detailed and structured information about document text
    if ($annotation) {
        foreach ($annotation->getPages() as $page) {
            foreach ($page->getBlocks() as $block) {
                $block_text = '';
                foreach ($block->getParagraphs() as $paragraph) {
                    foreach ($paragraph->getWords() as $word) {
                        foreach ($word->getSymbols() as $symbol) {
                            $block_text .= $symbol->getText();
                        }
                        $block_text .= ' ';
                    }
                    $block_text .= "\n";
                }
                printf('Block content: %s', $block_text);
                printf('Block confidence: %f' . PHP_EOL,
                    $block->getConfidence());

                # get bounds
                $vertices = $block->getBoundingBox()->getVertices();
                $bounds = [];
                foreach ($vertices as $vertex) {
                    $bounds[] = sprintf('(%d,%d)', $vertex->getX(),
                        $vertex->getY());
                }
                print('Bounds: ' . join(', ', $bounds) . PHP_EOL);
                print(PHP_EOL);
            }
        }
    } else {
        print('No text found' . PHP_EOL);
    }

    $imageAnnotator->close();
}
// [END vision_fulltext_detection]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
