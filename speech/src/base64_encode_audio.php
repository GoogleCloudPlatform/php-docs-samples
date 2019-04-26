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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/speech/README.md
 */

if (count($argv) != 2) {
    return print("Usage: php base64_encode_audio.php AUDIO_FILE\n");
}
list($_, $audioFile) = $argv;

# [START base64_audio]
/** Uncomment and populate these variables in your code */
// $audioFile = 'path to an audio file';

$audioFileResource = fopen($audioFile, 'r');
$base64Audio = base64_encode(stream_get_contents($audioFileResource));
print($base64Audio);
# [end base64_audio]
