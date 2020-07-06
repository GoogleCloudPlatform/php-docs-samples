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

// [START dialogflow_create_intent]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\IntentsClient;
use Google\Cloud\Dialogflow\V2\Intent\TrainingPhrase\Part;
use Google\Cloud\Dialogflow\V2\Intent\TrainingPhrase;
use Google\Cloud\Dialogflow\V2\Intent\Message\Text;
use Google\Cloud\Dialogflow\V2\Intent\Message;
use Google\Cloud\Dialogflow\V2\Intent;

/**
* Create an intent of the given intent type.
*/
function intent_create($projectId, $displayName, $trainingPhraseParts = [],
    $messageTexts = [])
{
    $intentsClient = new IntentsClient();

    // prepare parent
    $parent = $intentsClient->agentName($projectId);

    // prepare training phrases for intent
    $trainingPhrases = [];
    foreach ($trainingPhraseParts as $trainingPhrasePart) {
        $part = (new Part())
            ->setText($trainingPhrasePart);

        // create new training phrase for each provided part
        $trainingPhrase = (new TrainingPhrase())
            ->setParts([$part]);
        $trainingPhrases[] = $trainingPhrase;
    }

    // prepare messages for intent
    $text = (new Text())
        ->setText($messageTexts);
    $message = (new Message())
        ->setText($text);

    // prepare intent
    $intent = (new Intent())
        ->setDisplayName($displayName)
        ->setTrainingPhrases($trainingPhrases)
        ->setMessages([$message]);

    // create intent
    $response = $intentsClient->createIntent($parent, $intent);
    printf('Intent created: %s' . PHP_EOL, $response->getName());

    $intentsClient->close();
}
// [END dialogflow_create_intent]
