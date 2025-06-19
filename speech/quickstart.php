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

# [START speech_quickstart]
// Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

// Imports the Google Cloud client library
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig;
use Google\Cloud\Speech\V2\Recognizer;
use Google\Cloud\Speech\V2\RecognizeRequest;
use Google\Cloud\Speech\V2\CreateRecognizerRequest;
use Google\Cloud\Speech\V2\GetRecognizerRequest;
use Google\ApiCore\ApiException;

// The name of the audio file to transcribe
$gcsUri = 'gs://cloud-samples-data/speech/brooklyn_bridge.raw';

// Populate these variables with your own values
$projectId = 'YOUR_PROJECT_ID';
$recognizerId = 'speech-v2-recognizer-php-quickstart';
$location = 'global';

// Instantiates a client
$client = new SpeechClient();

// The name of the recognizer to create
$recognizerName = $client->recognizerName($projectId, $location, $recognizerId);
$getRecognizerRequest = (new GetRecognizerRequest())->setName($recognizerName);

try {
    $recognizer = $client->getRecognizer($getRecognizerRequest);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        // If the recognizer does not exist, create it.

        // Create an explicit decoding config because .raw files have no header.
        $explicitConfig = (new ExplicitDecodingConfig())
            ->setEncoding(ExplicitDecodingConfig\AudioEncoding::LINEAR16)
            ->setSampleRateHertz(16000)
            ->setAudioChannelCount(1); // The brooklyn_bridge audio is single-channel (mono)

        $config = (new RecognitionConfig())
            ->setLanguageCodes(['en-US'])
            ->setModel('long') // Or other models like 'telephony', 'medical_dictation'
            ->setExplicitDecodingConfig($explicitConfig);

        $recognizer = (new Recognizer())
            ->setName($recognizerName)
            ->setDefaultRecognitionConfig($config);

        $createRecognizerRequest = (new CreateRecognizerRequest())
            ->setParent($client->locationName($projectId, $location))
            ->setRecognizer($recognizer)
            ->setRecognizerId($recognizerId);
        $operation = $client->createRecognizer($createRecognizerRequest);

        $operation->pollUntilComplete();
        $recognizer = $operation->getResult();
        printf('Created Recognizer: %s' . PHP_EOL, $recognizer->getName());
    } else {
        throw $e;
    }
}

// Detects speech in the audio file
$recognizeRequest = (new RecognizeRequest())
    ->setRecognizer($recognizerName)
    ->setUri($gcsUri);
$response = $client->recognize($recognizeRequest);

// Print most likely transcription
foreach ($response->getResults() as $result) {
    $alternatives = $result->getAlternatives();
    $mostLikely = $alternatives[0];
    $transcript = $mostLikely->getTranscript();
    printf('Transcript: %s' . PHP_EOL, $transcript);
}

$client->close();

# [END speech_quickstart]
