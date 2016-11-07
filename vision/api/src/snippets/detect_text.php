<?php

/**
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// [START text_detection]
use Google\Cloud\Vision\VisionClient;

// $apiKey = 'YOUR-API-KEY';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'key' => $apiKey,
]);
$image = $vision->image(file_get_contents($path), ['TEXT_DETECTION']);
$result = $vision->annotate($image);
if (!isset($result->info()['textAnnotations'])) {
    return;
}
foreach ($result->info()['textAnnotations'] as $annotation) {
    print("TEXT\n");
    if (isset($annotation['locale'])) {
        print("  locale: $annotation[locale]\n");
    }
    print("  description: $annotation[description]\n");
    if (isset($annotation['boundingPoly'])) {
        print("  BOUNDING POLY\n");
        foreach ($annotation['boundingPoly']['vertices'] as $vertex) {
            print("    x:$vertex[x]\ty:$vertex[y]\n");
        }
    }
}
// [END text_detection]
