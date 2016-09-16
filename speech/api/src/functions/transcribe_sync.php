<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/speech/README.md
 */

namespace Google\Cloud\Samples\Speech;

# [START transcribe_sync]
use Google\Cloud\ServiceBuilder;

/**
 * Transcribe an audio file using Google Cloud Speech API
 * Example:
 * ```
 * transcribe_sync('/path/to/audiofile.wav');
 * ```.
 *
 * @param string $audioFile path to an audio file.
 *
 * @return string the text transcription
 */
function transcribe_sync($audioFile, $options = [])
{
    $builder = new ServiceBuilder();
    $speech = $builder->speech();
    $results = $speech->recognize(
        fopen($audioFile, 'r'),
        $options
    );
    print_r($results);
}
# [END transcribe_sync]
