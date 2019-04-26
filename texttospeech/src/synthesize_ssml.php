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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/texttospeech/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 2) {
    return print("Usage: php synthesize_ssml.php TEXT\n");
}
list($_, $ssml) = $argv;

// [START tts_synthesize_ssml]
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

/** Uncomment and populate these variables in your code */
// $ssml = 'SSML to synthesize';

// create client object
$client = new TextToSpeechClient();

$input_text = (new SynthesisInput())
    ->setSsml($ssml);

// note: the voice can also be specified by name
// names of voices can be retrieved with $client->listVoices()
$voice = (new VoiceSelectionParams())
    ->setLanguageCode('en-US')
    ->setSsmlGender(SsmlVoiceGender::FEMALE);

$audioConfig = (new AudioConfig())
    ->setAudioEncoding(AudioEncoding::MP3);

$response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);
$audioContent = $response->getAudioContent();

file_put_contents('output.mp3', $audioContent);
print('Audio content written to "output.mp3"' . PHP_EOL);

$client->close();
// [END tts_synthesize_ssml]
