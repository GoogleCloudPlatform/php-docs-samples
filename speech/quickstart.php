<?php
/**
 * Copyright 2023 Google LLC.
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
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library

use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\CreateRecognizerRequest;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig\AudioEncoding;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\Recognizer;
use Google\Cloud\Speech\V2\RecognizeRequest;

# The name of the audio file to transcribe
$gcsURI = 'gs://cloud-samples-data/speech/brooklyn_bridge.raw';

# Your Google Cloud Project ID and location
$projectId = 'YOUR_PROJECT_ID';
$location = 'global';

# Instantiates a client
$speech = new SpeechClient();

// Create a Recognizer
$createRecognizerRequest = new CreateRecognizerRequest([
    'parent' => SpeechClient::locationName($projectId, $location),
    'recognizer_id' => $recognizerId = 'quickstart-recognizer-' . uniqid(),
    'recognizer' => new Recognizer([
        'language_codes' => ['en-US'],
        'model' => 'latest_short'
    ])
]);

$operation = $speech->createRecognizer($createRecognizerRequest);

// Wait for the operation to complete
$operation->pollUntilComplete();
if ($operation->operationSucceeded()) {
    $result = $operation->getResult();
    printf('Created Recognizer: %s' . PHP_EOL, $result->getName());
} else {
    print_r($operation->getError());
}

$recognitionConfig = (new RecognitionConfig())
    ->setExplicitDecodingConfig(new ExplicitDecodingConfig([
        'encoding' => AudioEncoding::LINEAR16,
        'sample_rate_hertz' => 16000,
        'audio_channel_count' => 1,
    ]));

$recognizerName = SpeechClient::recognizerName($projectId, $location, $recognizerId);
$request = (new RecognizeRequest())
    ->setRecognizer($recognizerName)
    ->setConfig($recognitionConfig)
    ->setUri($gcsURI);

# Detects speech in the audio file
$response = $speech->recognize($request);

# Print most likely transcription
foreach ($response->getResults() as $result) {
    $alternatives = $result->getAlternatives();
    $mostLikely = $alternatives[0];
    $transcript = $mostLikely->getTranscript();
    printf('Transcript: %s' . PHP_EOL, $transcript);
}

$speech->close();
