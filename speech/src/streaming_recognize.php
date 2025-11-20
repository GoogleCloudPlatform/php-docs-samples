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

# [START speech_transcribe_streaming]
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\StreamingRecognizeRequest;
use Google\Cloud\Speech\V2\RecognitionFeatures;
use Google\Cloud\Speech\V2\AutoDetectDecodingConfig;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\StreamingRecognitionConfig;

/**
 * @param string $projectId The Google Cloud project ID.
 * @param string $location The location of the recognizer.
 * @param string $recognizerId The ID of the recognizer to use.
 * @param string $audioFile path to an audio file.
 */
function streaming_recognize(string $projectId, string $location, string $recognizerId, string $audioFile)
{
    // create the speech client
    $apiEndpoint = $location === 'global' ? null : sprintf('%s-speech.googleapis.com', $location);
    $speech = new SpeechClient(['apiEndpoint' => $apiEndpoint]);

    $recognizerName = SpeechClient::recognizerName($projectId, $location, $recognizerId);

    // set streaming config
    $features = new RecognitionFeatures([
        'enable_automatic_punctuation' => true
    ]);
    $streamingConfig = (new StreamingRecognitionConfig())
        ->setConfig(new RecognitionConfig([
            // Can also use {@see Google\Cloud\Speech\V2\ExplicitDecodingConfig}
            'auto_decoding_config' => new AutoDetectDecodingConfig(),
            'features' => $features
        ]));
    $streamingRequest = (new StreamingRecognizeRequest())
        ->setRecognizer($recognizerName)
        ->setStreamingConfig($streamingConfig);

    // set the streaming request
    $stream = $speech->streamingRecognize();
    $stream->write($streamingRequest);

    // stream the audio file
    $handle = fopen($audioFile, 'r');
    while (!feof($handle)) {
        $chunk = fread($handle, 4096);
        $streamingRequest = (new StreamingRecognizeRequest())
            ->setAudio($chunk);
        $stream->write($streamingRequest);
    }
    fclose($handle);

    // read the responses
    foreach ($stream->closeWriteAndReadAll() as $response) {
        // an empty response indicates the end of the stream
        if (!$response->getResults()) {
            continue;
        }

        // process the results
        foreach ($response->getResults() as $result) {
            printf(
                'Transcription: "%s"' . PHP_EOL,
                $result->getAlternatives()[0]->getTranscript()
            );
        }
    }
}
# [END speech_transcribe_streaming]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
