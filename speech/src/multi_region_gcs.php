<?php
/**
 * Copyright 2021 Google Inc.
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

namespace Google\Cloud\Samples\Speech;

# [START speech_transcribe_with_multi_region_gcs]
# Imports the Google Cloud client library
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;
use Google\Cloud\Speech\V1\SpeechClient;

/**
 * @param string $uri The Cloud Storage object to transcribe
 *                    e.x. gs://cloud-samples-data/speech/brooklyn_bridge.raw
 */
function multi_region_gcs(string $uri)
{
    # set string as audio content
    $audio = (new RecognitionAudio())
        ->setUri($uri);

    # The audio file's encoding, sample rate and language
    $config = new RecognitionConfig([
        'encoding' => AudioEncoding::LINEAR16,
        'sample_rate_hertz' => 16000,
        'language_code' => 'en-US'
    ]);

    # Specify a new endpoint.
    $options = ['apiEndpoint' => 'eu-speech.googleapis.com'];

    # Instantiates a client
    $client = new SpeechClient($options);

    # Detects speech in the audio file
    $response = $client->recognize($config, $audio);

    # Print most likely transcription
    foreach ($response->getResults() as $result) {
        $alternatives = $result->getAlternatives();
        $mostLikely = $alternatives[0];
        $transcript = $mostLikely->getTranscript();
        printf('Transcript: %s' . PHP_EOL, $transcript);
    }

    $client->close();
}
# [END speech_transcribe_with_multi_region_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
