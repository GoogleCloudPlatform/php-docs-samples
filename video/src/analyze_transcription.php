<?php

/**
 * Copyright 2018 Google Inc.
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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 2 || count($argv) > 3) {
    return print("Usage: php analyze_transcription.php URI\n");
}
list($_, $uri) = $argv;
$options = isset($argv[2]) ? ['pollingIntervalSeconds' => $argv[2]] : [];

// [START video_speech_transcription_gcs]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;
use Google\Cloud\VideoIntelligence\V1\VideoContext;
use Google\Cloud\VideoIntelligence\V1\SpeechTranscriptionConfig;

/** Uncomment and populate these variables in your code */
// $uri = 'The cloud storage object to analyze (gs://your-bucket-name/your-object-name)';
// $options = [];

# set configs
$features = [Feature::SPEECH_TRANSCRIPTION];
$speechTranscriptionConfig = (new SpeechTranscriptionConfig())
    ->setLanguageCode('en-US')
    ->setEnableAutomaticPunctuation(true);
$videoContext = (new VideoContext())
    ->setSpeechTranscriptionConfig($speechTranscriptionConfig);

# instantiate a client
$client = new VideoIntelligenceServiceClient();

# execute a request.
$operation = $client->annotateVideo([
    'inputUri' => $uri,
    'features' => $features,
    'videoContext' => $videoContext
]);

print('Processing video for speech transcription...' . PHP_EOL);
# Wait for the request to complete.
$operation->pollUntilComplete($options);

# Print the result.
if ($operation->operationSucceeded()) {
    $result = $operation->getResult();
    # there is only one annotation_result since only
    # one video is processed.
    $annotationResults = $result->getAnnotationResults()[0];
    $speechTranscriptions = $annotationResults ->getSpeechTranscriptions();

    foreach ($speechTranscriptions as $transcription) {
        # the number of alternatives for each transcription is limited by
        # $max_alternatives in SpeechTranscriptionConfig
        # each alternative is a different possible transcription
        # and has its own confidence score.
        foreach ($transcription->getAlternatives() as $alternative) {
            print('Alternative level information' . PHP_EOL);

            printf('Transcript: %s' . PHP_EOL, $alternative->getTranscript());
            printf('Confidence: %s' . PHP_EOL, $alternative->getConfidence());

            print('Word level information:');
            foreach ($alternative->getWords() as $wordInfo) {
                printf(
                    '%s s - %s s: %s' . PHP_EOL,
                    $wordInfo->getStartTime()->getSeconds(),
                    $wordInfo->getEndTime()->getSeconds(),
                    $wordInfo->getWord()
                );
            }
        }
    }
}
$client->close();
// [END video_speech_transcription_gcs]
