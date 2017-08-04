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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/speech/api/README.md
 */

namespace Google\Cloud\Samples\Speech;

# [START transcribe_sync]
use Google\Cloud\Speech\SpeechClient;

/**
 * Transcribe an audio file using Google Cloud Speech API
 * Example:
 * ```
 * transcribe_sync('/path/to/audiofile.wav');
 * ```.
 *
 * @param string $audioFile path to an audio file.
 * @param string $languageCode The language of the content to
 *     be recognized. Accepts BCP-47 (e.g., `"en-US"`, `"es-ES"`).
 * @param array $options configuration options.
 *
 * @return string the text transcription
 */
function transcribe_sync($audioFile, $languageCode = 'en-US', $options = [])
{
    // Create the speech client
    $speech = new SpeechClient([
        'languageCode' => $languageCode,
    ]);

    // Make the API call
    $results = $speech->recognize(
        fopen($audioFile, 'r'),
        $options
    );

    // Print the results
    $alternatives = $results[0]->alternatives();
    foreach ($alternatives as $alternative) {
        printf('Transcript: %s' . PHP_EOL, $alternative['transcript']);
        printf('Confidence: %s' . PHP_EOL, $alternative['confidence']);
    }
}
# [END transcribe_sync]
