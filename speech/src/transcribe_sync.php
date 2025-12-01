<?php
/**
 * Copyright 2023 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/speech/README.md
 */

namespace Google\Cloud\Samples\Speech;

# [START speech_transcribe_sync]

use Google\Cloud\Speech\V2\AutoDetectDecodingConfig;
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\RecognizeRequest;

/**
 * @param string $projectId The Google Cloud project ID.
 * @param string $recognizerId The ID of the recognizer to use.
 * @param string $audioFile path to an audio file (e.x. "test/data/audio32KHz.flac")
 */
function transcribe_sync(string $projectId, string $location, string $recognizerId, string $audioFile)
{
    // create the speech client
    $apiEndpoint = $location === 'global' ? null : sprintf('%s-speech.googleapis.com', $location);
    $speech = new SpeechClient(['apiEndpoint' => $apiEndpoint]);

    // get contents of a file into a string
    $content = file_get_contents($audioFile);

    $recognizerName = SpeechClient::recognizerName($projectId, $location, $recognizerId);

    $config = (new RecognitionConfig())

        // Can also use {@see Google\Cloud\Speech\V2\ExplicitDecodingConfig}
        // ->setExplicitDecodingConfig(new ExplicitDecodingConfig([...]);

        ->setAutoDecodingConfig(new AutoDetectDecodingConfig());

    $request = (new RecognizeRequest())
        ->setRecognizer($recognizerName)
        ->setContent($content)
        ->setConfig($config);

    try {
        $response = $speech->recognize($request);
        foreach ($response->getResults() as $result) {
            $alternatives = $result->getAlternatives();
            $mostLikely = $alternatives[0];
            $transcript = $mostLikely->getTranscript();
            $confidence = $mostLikely->getConfidence();
            printf('Transcript: %s' . PHP_EOL, $transcript);
            printf('Confidence: %s' . PHP_EOL, $confidence);
        }
    } finally {
        $speech->close();
    }
}
# [END speech_transcribe_sync]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
