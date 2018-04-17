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

// [START dialogflow_detect_intent_audio]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\AudioEncoding;
use Google\Cloud\Dialogflow\V2\InputAudioConfig;
use Google\Cloud\Dialogflow\V2\QueryInput;

/**
* Returns the result of detect intent with an audio file as input.
* Using the same `session_id` between requests allows continuation
* of the conversation.
*/
function detect_intent_audio($projectId, $path, $sessionId, $languageCode = 'en-US')
{
    // new session
    $sessionsClient = new SessionsClient();
    $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());
    printf('Session path: %s' . PHP_EOL, $session);

    // load audio file
    $inputAudio = file_get_contents($path);

    // hard coding audio_encoding and sample_rate_hertz for simplicity
    $audioConfig = new InputAudioConfig();
    $audioConfig->setAudioEncoding(AudioEncoding::AUDIO_ENCODING_LINEAR_16);
    $audioConfig->setLanguageCode($languageCode);
    $audioConfig->setSampleRateHertz(16000);

    // create query input
    $queryInput = new QueryInput();
    $queryInput->setAudioConfig($audioConfig);

    // get response and relevant info
    $response = $sessionsClient->detectIntent($session, $queryInput, ['inputAudio' => $inputAudio]);
    $queryResult = $response->getQueryResult();
    $queryText = $queryResult->getQueryText();
    $intent = $queryResult->getIntent();
    $displayName = $intent->getDisplayName();
    $confidence = $queryResult->getIntentDetectionConfidence();
    $fulfilmentText = $queryResult->getFulfillmentText();

    // output relevant info
    print(str_repeat("=", 20) . PHP_EOL);
    printf('Query text: %s' . PHP_EOL, $queryText);
    printf('Detected intent: %s (confidence: %f)' . PHP_EOL, $displayName,
        $confidence);
    print(PHP_EOL);
    printf('Fulfilment text: %s' . PHP_EOL, $fulfilmentText);

    $sessionsClient->close();
}
// [END dialogflow_detect_intent_audio]
