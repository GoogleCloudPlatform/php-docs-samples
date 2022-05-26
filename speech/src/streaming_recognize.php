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

# [START speech_transcribe_streaming]
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognizeRequest;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

/**
 * @param string $audioFile path to an audio file
 */
function streaming_recognize(string $audioFile)
{
    // change these variables if necessary
    $encoding = AudioEncoding::LINEAR16;
    $sampleRateHertz = 32000;
    $languageCode = 'en-US';

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
}
# [END speech_transcribe_streaming]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
