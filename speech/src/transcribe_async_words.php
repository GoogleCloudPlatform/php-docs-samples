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

# [START speech_transcribe_async_word_time_offsets_gcs]

use Google\Cloud\Speech\V2\BatchRecognizeFileMetadata;
use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\BatchRecognizeRequest;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig;
use Google\Cloud\Speech\V2\ExplicitDecodingConfig\AudioEncoding;
use Google\Cloud\Speech\V2\InlineOutputConfig;
use Google\Cloud\Speech\V2\RecognitionConfig;
use Google\Cloud\Speech\V2\RecognitionFeatures;
use Google\Cloud\Speech\V2\RecognitionOutputConfig;
use Google\Cloud\Speech\V2\SpeakerDiarizationConfig;

/**
 * @param string $projectId The Google Cloud project ID.
 * @param string $location The location of the recognizer.
 * @param string $recognizerId The ID of the recognizer to use. The recognizer's model must support
 *                             diarization (e.g. "chirp_3").
 * @param string $uri The Cloud Storage object to transcribe (other than global)
 *                    e.x. gs://cloud-samples-data/speech/brooklyn_bridge.raw
 */
function transcribe_async_words(string $projectId, string $location, string $recognizerId, string $uri)
{
    $apiEndpoint = $location === 'global' ? null : sprintf('%s-speech.googleapis.com', $location);
    $speech = new SpeechClient(['apiEndpoint' => $apiEndpoint]);
    $recognizerName = SpeechClient::recognizerName($projectId, $location, $recognizerId);

    // When this is enabled, we send all the words from the beginning of the audio.
    $features = new RecognitionFeatures([
        'diarization_config' => new SpeakerDiarizationConfig(),
    ]);

    $config = (new RecognitionConfig())
        ->setFeatures($features)
        // When running outside the "global" location, you can set the model to "chirp_3" in
        // RecognitionConfig instead of on the recognizer.
        // ->setModel('chirp_3')

        // Can also use {@see Google\Cloud\Speech\V2\AutoDetectDecodingConfig}
        // ->setAutoDecodingConfig(new AutoDetectDecodingConfig());

        ->setExplicitDecodingConfig(new ExplicitDecodingConfig([
            // change these variables if necessary
            'encoding' => AudioEncoding::LINEAR16,
            'sample_rate_hertz' => 16000,
            'audio_channel_count' => 1,
        ]));

    $outputConfig = (new RecognitionOutputConfig())
        ->setInlineResponseConfig(new InlineOutputConfig());

    $file = new BatchRecognizeFileMetadata();
    $file->setUri($uri);

    $request = (new BatchRecognizeRequest())
        ->setRecognizer($recognizerName)
        ->setConfig($config)
        ->setFiles([$file])
        ->setRecognitionOutputConfig($outputConfig);

    try {
        $operation = $speech->batchRecognize($request);
        $operation->pollUntilComplete();

        if ($operation->operationSucceeded()) {
            $response = $operation->getResult();
            foreach ($response->getResults() as $result) {
                if ($result->getError()) {
                    print('Error: '. $result->getError()->getMessage());
                }
                // get the most likely transcription
                $transcript = $result->getInlineResult()->getTranscript();
                foreach ($transcript->getResults() as $transacriptResult) {
                    $alternatives = $transacriptResult->getAlternatives();
                    $mostLikely = $alternatives[0];
                    foreach ($mostLikely->getWords() as $wordInfo) {
                        $startTime = $wordInfo->getStartOffset();
                        $endTime = $wordInfo->getEndOffset();
                        printf('  Word: %s (start: %s, end: %s)' . PHP_EOL,
                            $wordInfo->getWord(),
                            $startTime?->serializeToJsonString(),
                            $endTime?->serializeToJsonString()
                        );
                    }
                }
            }
        } else {
            print_r($operation->getError());
        }
    } finally {
        $speech->close();
    }
}
# [END speech_transcribe_async_word_time_offsets_gcs]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
