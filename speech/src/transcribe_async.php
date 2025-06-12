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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/speech/README.md
 */

namespace Google\Cloud\Samples\Speech;

# [START speech_transcribe_async]
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\RecognitionAudio;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig\AudioEncoding;

/**
 * @param string $audioFile path to an audio file
 */
function transcribe_async(string $audioFile)
{
    // change these variables if necessary
    $encoding = AudioEncoding::LINEAR16;
    $sampleRateHertz = 32000;
    $languageCode = 'en-US';

    // get contents of a file into a string
    $content = file_get_contents($audioFile);

    // set string as audio content
    $audio = (new RecognitionAudio())
        ->setContent($content);

    // set config
    $config = (new RecognitionConfig())
        ->setEncoding($encoding)
        ->setSampleRateHertz($sampleRateHertz)
        ->setLanguageCode($languageCode);

    // create the speech client
    $client = new SpeechClient();

    // create the asyncronous recognize operation
    $operation = $client->longRunningRecognize($config, $audio);
    $operation->pollUntilComplete();

    if ($operation->operationSucceeded()) {
        $response = $operation->getResult();

        // each result is for a consecutive portion of the audio. iterate
        // through them to get the transcripts for the entire audio file.
        foreach ($response->getResults() as $result) {
            $alternatives = $result->getAlternatives();
            $mostLikely = $alternatives[0];
            $transcript = $mostLikely->getTranscript();
            $confidence = $mostLikely->getConfidence();
            printf('Transcript: %s' . PHP_EOL, $transcript);
            printf('Confidence: %s' . PHP_EOL, $confidence);
        }
    } else {
        print_r($operation->getError());
    }

    $client->close();
}
# [END speech_transcribe_async]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
