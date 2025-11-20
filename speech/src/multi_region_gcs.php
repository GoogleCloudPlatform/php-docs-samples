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

namespace Google\Cloud\Samples\Speech;

# [START speech_transcribe_with_multi_region_gcs]
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig\AudioEncoding;
use Google\Cloud\Speech\V2\RecognizeRequest;

/**
 * @param string $projectId The Google Cloud project ID.
 * @param string $location The location of the recognizer.
 * @param string $recognizerId The ID of the recognizer to use (other than global).
 * @param string $uri The Cloud Storage object to transcribe
 *                    e.x. gs://cloud-samples-data/speech/brooklyn_bridge.raw
 */
function multi_region_gcs(string $projectId, string $location, string $recognizerId, string $uri)
{
    $options = ['apiEndpoint' => sprintf('%s-speech.googleapis.com', $location)];
    $speech = new SpeechClient($options);

    $recognizerName = SpeechClient::recognizerName($projectId, $location, $recognizerId);

    $config = (new RecognitionConfig())
        // Can also use {@see Google\Cloud\Speech\V2\AutoDetectDecodingConfig}
        // ->setAutoDecodingConfig(new AutoDetectDecodingConfig());

        ->setExplicitDecodingConfig(new ExplicitDecodingConfig([
            'encoding' => AudioEncoding::LINEAR16,
            'sample_rate_hertz' => 16000,
            'audio_channel_count' => 1,
        ]));

    $request = (new RecognizeRequest())
        ->setRecognizer($recognizerName)
        ->setConfig($config)
        ->setUri($uri);

    # Detects speech in the audio file
    $response = $speech->recognize($request);

    # Print most likely transcription
    foreach ($response->getResults() as $result) {
        $alternatives = $result->getAlternatives();
        $mostLikely = $alternatives[0];
        $transcript = $mostLikely->getTranscript();
        printf('Transcript: %s' . PHP_EOL, $transcript);
    }
}
# [END speech_transcribe_with_multi_region_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
