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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 2) {
    return print("Usage: php transcribe_asyc_words.php AUDIO_FILE\n");
}
list($_, $audioFile) = $argv;

# [START speech_transcribe_async_word_time_offsets_gcs]
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

/** Uncomment and populate these variables in your code */
// $audioFile = 'path to an audio file';

// change these variables if necessary
$encoding = AudioEncoding::LINEAR16;
$sampleRateHertz = 32000;
$languageCode = 'en-US';

if (!extension_loaded('grpc')) {
    throw new \Exception('Install the grpc extension (pecl install grpc)');
}

// When true, time offsets for every word will be included in the response.
$enableWordTimeOffsets = true;

// get contents of a file into a string
$content = file_get_contents($audioFile);

// set string as audio content
$audio = (new RecognitionAudio())
    ->setContent($content);

// set config
$config = (new RecognitionConfig())
    ->setEncoding($encoding)
    ->setSampleRateHertz($sampleRateHertz)
    ->setLanguageCode($languageCode)
    ->setEnableWordTimeOffsets($enableWordTimeOffsets);

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
        foreach ($mostLikely->getWords() as $wordInfo) {
            $startTime = $wordInfo->getStartTime();
            $endTime = $wordInfo->getEndTime();
            printf('  Word: %s (start: %s, end: %s)' . PHP_EOL,
                $wordInfo->getWord(),
                $startTime->serializeToJsonString(),
                $endTime->serializeToJsonString());
        }
    }
} else {
    print_r($operation->getError());
}

$client->close();
# [END speech_transcribe_async_word_time_offsets_gcs]
