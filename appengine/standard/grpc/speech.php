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

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognitionConfig;
use Google\Cloud\Speech\V1\StreamingRecognizeRequest;
use Google\Cloud\Speech\V1\RecognitionConfig_AudioEncoding;

$audioFile = __DIR__ . '/audio32KHz.raw';
$languageCode = 'en-US';
$sampleRateHertz = '32000';
$encoding = 'LINEAR16';

$speechClient = new SpeechClient();
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
