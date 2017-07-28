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

use Google\Cloud\Vision\VisionClient;

// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

function detect_document_text($projectId, $path)
{
    $vision = new VisionClient([
        'projectId' => $projectId,
    ]);

    # Annotate the image
    $image = $vision->image(
        file_get_contents($path), ['DOCUMENT_TEXT_DETECTION']);
    $annotation = $vision->annotate($image);

    # Print out document text
    $document = $annotation->fullText();
    $text = $document->text();
    printf('Document text: %s' . PHP_EOL, $text);

    # Print out more detailed and structured information about document text
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
            printf('Block text: %s' . PHP_EOL, $block_text);
            printf('Block bounds:' . PHP_EOL);
            foreach ($block['boundingBox']['vertices'] as $vertice) {
                printf('X: %s Y: %s' . PHP_EOL, $vertice['x'], $vertice['y']);
            }
            printf(PHP_EOL);
        }
    }
}
// [END vision_fulltext_detection]
