<?php
# Copyright 2023 Google LLC
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

namespace Google\Cloud\Samples\Speech;

# [START speech_profanity_filter]
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\RecognizeRequest;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\RecognitionFeatures;
use Google\Cloud\Speech\V2\AutoDetectDecodingConfig;

/**
 * @param string $projectId The Google Cloud project ID.
 * @param string $location The location of the recognizer.
 * @param string $recognizerId The ID of the recognizer to use.
 * @param string $audioFile path to an audio file.
 *               ex "test/data/audio32KHz.flac"
 */
function profanity_filter(string $projectId, string $location, string $recognizerId, string $audioFile)
{
    // create the speech client
    $apiEndpoint = $location === 'global' ? null : sprintf('%s-speech.googleapis.com', $location);
    $speech = new SpeechClient(['apiEndpoint' => $apiEndpoint]);

    // get contents of a file into a string
    $content = file_get_contents($audioFile);

    $recognizerName = SpeechClient::recognizerName($projectId, $location, $recognizerId);

    // When true, the profanity filter will be enabled.
    $features = new RecognitionFeatures([
        'profanity_filter' => true
    ]);

    $config = (new RecognitionConfig())
        ->setFeatures($features)

        // Can also use {@see Google\Cloud\Speech\V2\ExplicitDecodingConfig}
        // ->setExplicitDecodingConfig(new ExplicitDecodingConfig([...]);

        ->setAutoDecodingConfig(new AutoDetectDecodingConfig());

    $request = (new RecognizeRequest())
        ->setRecognizer($recognizerName)
        ->setConfig($config)
        ->setContent($content);

    # Detects speech in the audio file
    $response = $speech->recognize($request);

    # Print most likely transcription
    foreach ($response->getResults() as $result) {
        $transcript = $result->getAlternatives()[0]->getTranscript();
        printf('Transcript: %s' . PHP_EOL, $transcript);
    }

    $speech->close();
}
# [END speech_profanity_filter]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
