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


// [START safe_search_detection]
use Google\Cloud\Vision\VisionClient;

// $apiKey = 'YOUR-API-KEY';
// $path = 'path/to/your/image.jpg'

$vision = new VisionClient([
    'key' => $apiKey,
]);
$image = $vision->image(file_get_contents($path), [
    'SAFE_SEARCH_DETECTION'
]);
$result = $vision->annotate($image);
$safe = $result->safeSearch();
printf("Adult: %s\n", $safe->isAdult() ? 'yes' : 'no');
printf("Spoof: %s\n", $safe->isSpoof() ? 'yes' : 'no');
printf("Medical: %s\n", $safe->isMedical() ? 'yes' : 'no');
printf("Violence: %s\n\n", $safe->isViolent() ? 'yes' : 'no');
// [END safe_search_detection]
