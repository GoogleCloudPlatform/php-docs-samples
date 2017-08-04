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

# [START streaming_recognize]
use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognizeRequest;
use Google\Cloud\Speech\V1\RecognitionConfig_AudioEncoding;

/**
 * Transcribe an audio file using Google Cloud Speech API
 * Example:
 * ```
 * $audoEncoding =  Google\Cloud\Speech\V1\RecognitionConfig_AudioEncoding::WAV
 * streaming_recognize('/path/to/audiofile.wav', 'en-US');
 * ```.
 *
 * @param string $audioFile path to an audio file.
 * @param string $languageCode The language of the content to
 *     be recognized. Accepts BCP-47 (e.g., `"en-US"`, `"es-ES"`).
 * @param string $encoding
 * @param string $sampleRateHertz
 *
 * @return string the text transcription
 */
function streaming_recognize($audioFile, $languageCode, $encoding, $sampleRateHertz)
{
    if (!defined('Grpc\STATUS_OK')) {
        throw new \Exception('Install the grpc extension ' .
            '(pecl install grpc)');
    }
    if (!class_exists('Google\Cloud\Speech\V1\SpeechGrpcClient')) {
        throw new \Exception('Install the proto client PHP library ' .
            '(composer require google/proto-client)');
    }
    if (!class_exists('Google\GAX\GrpcConstants')) {
        throw new \Exception('Install the GAX library ' .
            '(composer require google/gax)');
    }

    $speechClient = new SpeechClient();
    try {
        $config = new RecognitionConfig();
        $config->setLanguageCode($languageCode);
        $config->setSampleRateHertz($sampleRateHertz);
        // encoding must be an enum, convert from string
        $encodingEnum = constant(RecognitionConfig_AudioEncoding::class . '::' . $encoding);
        $config->setEncoding($encodingEnum);

        $strmConfig = new StreamingRecognitionConfig();
        $strmConfig->setConfig($config);

        $strmReq = new StreamingRecognizeRequest();
        $strmReq->setStreamingConfig($strmConfig);

        $strm = $speechClient->streamingRecognize();
        $strm->write($strmReq);

        $strmReq = new StreamingRecognizeRequest();
        $f = fopen($audioFile, "rb");
        $fsize = filesize($audioFile);
        $bytes = fread($f, $fsize);
        $strmReq->setAudioContent($bytes);
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
# [END streaming_recognize]
