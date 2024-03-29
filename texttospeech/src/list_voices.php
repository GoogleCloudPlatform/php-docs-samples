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

/**
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/texttospeech/README.md
 */

namespace Google\Cloud\Samples\TextToSpeech;

// [START tts_list_voices]
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\ListVoicesRequest;

function list_voices(): void
{
    // create client object
    $client = new TextToSpeechClient();

    // perform list voices request
    $request = (new ListVoicesRequest());
    $response = $client->listVoices($request);
    $voices = $response->getVoices();

    foreach ($voices as $voice) {
        // display the voice's name. example: tpc-vocoded
        printf('Name: %s' . PHP_EOL, $voice->getName());

        // display the supported language codes for this voice. example: 'en-US'
        foreach ($voice->getLanguageCodes() as $languageCode) {
            printf('Supported language: %s' . PHP_EOL, $languageCode);
        }

        // SSML voice gender values from TextToSpeech\V1\SsmlVoiceGender
        $ssmlVoiceGender = ['SSML_VOICE_GENDER_UNSPECIFIED', 'MALE', 'FEMALE',
        'NEUTRAL'];

        // display the SSML voice gender
        $gender = $voice->getSsmlGender();
        printf('SSML voice gender: %s' . PHP_EOL, $ssmlVoiceGender[$gender]);

        // display the natural hertz rate for this voice
        printf('Natural Sample Rate Hertz: %d' . PHP_EOL,
            $voice->getNaturalSampleRateHertz());
    }

    $client->close();
}
// [END tts_list_voices]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
