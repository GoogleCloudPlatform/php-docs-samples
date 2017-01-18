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

# [START detect_labels]
# [START import_libraries]
use Google\Cloud\Vision\VisionClient;

# [END import_libraries]

// $projectId = 'YOUR_PROJECT_ID';
// $path = 'path/to/your/image.jpg'

# [START authenticate]
$vision = new VisionClient([
    'projectId' => $projectId,
]);
# [END authenticate]

# [START construct_request]
$image = $vision->image(file_get_contents($path), ['LABEL_DETECTION']);
$result = $vision->annotate($image);
# [END construct_request]

# [START parse_response]
print("LABELS:\n");
foreach ($result->labels() as $label) {
    print($label->description() . PHP_EOL);
}
# [END parse_response]
# [END detect_labels]
