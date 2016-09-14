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

use Exception;
# [START transcribe_async]
use Google\Cloud\ServiceBuilder;
use Google\Cloud\ExponentialBackoff;

/**
 * Transcribe an audio file using Google Cloud Speech API
 * Example:
 * ```
 * transcribe_async('/path/to/audiofile.wav');
 * ```.
 *
 * @param string $audioFile path to an audio file.
 *
 * @return string the text transcription
 */
function transcribe_async($audioFile, $options = [])
{
    $builder = new ServiceBuilder();
    $speech = $builder->speech();
    $operation = $speech->beginRecognizeOperation(
        fopen($audioFile, 'r'),
        $options
    );
    $backoff = new ExponentialBackoff(10);
    $backoff->execute(function () use ($operation) {
        print('Waiting for operation to complete' . PHP_EOL);
        $operation->reload();
        if (!$operation->isComplete()) {
            throw new Exception('Job has not yet completed', 500);
        }
    });

    if ($operation->isComplete()) {
        if (empty($results = $operation->results())) {
            $results = $operation->info();
        }
        print_r($results);
    }
}
# [END transcribe_async]
