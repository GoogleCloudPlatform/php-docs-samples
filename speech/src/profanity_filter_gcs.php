<?php
# Copyright 2020 Google LLC
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#    http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

# [START profanity_filter]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;


/** The Cloud Storage object to transcribe */
$uri = 'gs://cloud-samples-tests/speech/brooklyn.flac';

// set string as audio content
$audio = (new RecognitionAudio())
    ->setUri($uri);

// set config
$config = (new RecognitionConfig())
    ->setEncoding(AudioEncoding::FLAC)
    ->setSampleRateHertz(16000)
    ->setLanguageCode('en-US')
    ->setProfanityFilter(TRUE);

// create the speech client
$client = new SpeechClient();

# Detects speech in the audio file
$response = $client->recognize($config, $audio);

# Print most likely transcription
foreach ($response->getResults() as $result) {
    $transcript = $result->getAlternatives()[0]->getTranscript();
    printf('Transcript: %s' . PHP_EOL, $transcript);
}

$client->close();

# [END profanity_filter]