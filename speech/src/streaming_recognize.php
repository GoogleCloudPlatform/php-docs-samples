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
    return print("Usage: php streaming_recognize.php AUDIO_FILE\n");
}
list($_, $audioFile) = $argv;

# [START speech_transcribe_streaming]
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognizeRequest;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

/** Uncomment and populate these variables in your code */
// $audioFile = 'path to an audio file';

// change these variables if necessary
$encoding = AudioEncoding::LINEAR16;
$sampleRateHertz = 32000;
$languageCode = 'en-US';

// the gRPC extension is required for streaming
if (!extension_loaded('grpc')) {
    throw new \Exception('Install the grpc extension (pecl install grpc)');
}

$speechClient = new SpeechClient();
try {
    $config = (new RecognitionConfig())
        ->setEncoding($encoding)
        ->setSampleRateHertz($sampleRateHertz)
        ->setLanguageCode($languageCode);

    $strmConfig = new StreamingRecognitionConfig();
    $strmConfig->setConfig($config);

    $strmReq = new StreamingRecognizeRequest();
    $strmReq->setStreamingConfig($strmConfig);

    $strm = $speechClient->streamingRecognize();
    $strm->write($strmReq);

    $strmReq = new StreamingRecognizeRequest();
    $content = file_get_contents($audioFile);
    $strmReq->setAudioContent($content);
    $strm->write($strmReq);

    foreach ($strm->closeWriteAndReadAll() as $response) {
        foreach ($response->getResults() as $result) {
            foreach ($result->getAlternatives() as $alt) {
                printf("Transcription: %s\n", $alt->getTranscript());
            }
        }
    }
} finally {
    $speechClient->close();
}
# [END speech_transcribe_streaming]
