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

namespace Google\Cloud\Samples\Vision;

// [START label_detection]
use Google\Cloud\Vision\VisionClient;

// $apiKey = 'YOUR-API-KEY';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'key' => $apiKey,
]);
$image = $vision->image(file_get_contents($path), ['LABEL_DETECTION']);
$result = $vision->annotate($image);
if (!isset($result->info()['labelAnnotations'])) {
    return;
}
foreach ($result->info()['labelAnnotations'] as $annotation) {
    print("LABEL\n");
    print("  mid: $annotation[mid]\n");
    print("  description: $annotation[description]\n");
    print("  score: $annotation[score]\n");
}
// [END label_detection]
