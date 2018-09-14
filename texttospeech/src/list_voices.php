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

namespace Google\Cloud\Samples\TextToSpeech;

// [START tts_list_voices]
use Google\Cloud\V1\TextToSpeechClient;

function list_voice()
{
    // create client object
    $client = new TextToSpeechClient();

    // perform list voices request
    $voices = $client->listVoices();

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