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

namespace Google\Cloud\Samples\Vision;

// [START vision_fulltext_detection]
use Google\Cloud\Vision\VisionClient;

// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'projectId' => $projectId,
]);
$image = $vision->image(file_get_contents($path), ['DOCUMENT_TEXT_DETECTION']);
$annotation = $vision->annotate($image);
$document = $annotation->fullText();

# Print out unstructured document text
$text = $document->text();
print('Document text: ' . $text . PHP_EOL);

# Print out more detailed and structured information about document text
foreach ($document->pages() as $page) {
    foreach ($page['blocks'] as $block) {
        $block_text = '';
        foreach ($block['paragraphs'] as $paragraph) {
            foreach ($paragraph['words'] as $word) {
                foreach ($word['symbols'] as $symbol) {
                    $block_text = $block_text . $symbol['text'];
                }
            }
        }
        print('Block text: ' . $block_text . PHP_EOL);
        print('Block bounds:' . PHP_EOL);
        foreach ($block['boundingBox']['vertices'] as $vertice) {
            print('X: ' . $vertice['x'] . ' Y: ' . $vertice['y'] . PHP_EOL);
        }
        print(PHP_EOL);
    }
}
// [END vision_fulltext_detection]
