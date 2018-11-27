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

// [START vision_fulltext_detection_gcs]
namespace Google\Cloud\Samples\Vision;

use Google\Cloud\Vision\VisionClient;

// $path = 'path/to/your/image.jpg'

function detect_document_text_gcs($path)
{
    $vision = new VisionClient();

    # annotate the image
    $image = $vision->image($path,['DOCUMENT_TEXT_DETECTION']);
    $annotations = $vision->annotate($image);
    $document = $annotations->fullText();

    # print out detailed and structured information about document text
    if ($document) {
        foreach ($document->pages() as $page) {
            foreach ($page['blocks'] as $block) {
                $block_text = '';
                foreach ($block['paragraphs'] as $paragraph) {
                    foreach ($paragraph['words'] as $word) {
                        foreach ($word['symbols'] as $symbol) {
                            $block_text .= $symbol['text'];
                        }
                        $block_text .= ' ';
                    }
                    $block_text .= "\n";
                }
                printf('Block content: %s', $block_text);
                printf('Block confidence: %f' . PHP_EOL,
                    $block['confidence']);

                # get bounds
                $vertices = $block['boundingBox']['vertices'];
                $bounds = [];
                foreach ($vertices as $vertex) {
                    # get (x, y) coordinates if available.
                    $bounds[] = sprintf('(%d,%d)',
                        isset($vertex['x']) ? $vertex['x'] : 0,
                        isset($vertex['y']) ? $vertex['y'] : 0
                    );
                }
                print('Bounds: ' . join(', ',$bounds) . PHP_EOL);
                print(PHP_EOL);
            }
        }
    } else {
        print('No text found' . PHP_EOL);
    }
}
// [END vision_fulltext_detection_gcs]
