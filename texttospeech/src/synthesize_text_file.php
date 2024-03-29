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

// [START tts_synthesize_text_file]
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

/**
 * @param string $path The text file to be synthesized. (e.g., hello.txt)
 */
function synthesize_text_file(string $path): void
{
    // create client object
    $client = new TextToSpeechClient();

    // get text from file
    $text = file_get_contents($path);
    $input_text = (new SynthesisInput())
        ->setText($text);

    // note: the voice can also be specified by name
    // names of voices can be retrieved with $client->listVoices()
    $voice = (new VoiceSelectionParams())
        ->setLanguageCode('en-US')
        ->setSsmlGender(SsmlVoiceGender::FEMALE);

    $audioConfig = (new AudioConfig())
        ->setAudioEncoding(AudioEncoding::MP3);
    $request = (new SynthesizeSpeechRequest())
        ->setInput($input_text)
        ->setVoice($voice)
        ->setAudioConfig($audioConfig);

    $response = $client->synthesizeSpeech($request);
    $audioContent = $response->getAudioContent();

    file_put_contents('output.mp3', $audioContent);
    print('Audio content written to "output.mp3"' . PHP_EOL);

    $client->close();
}
// [END tts_synthesize_text_file]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
