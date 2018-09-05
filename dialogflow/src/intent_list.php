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

// [START dialogflow_list_intents]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\IntentsClient;

function intent_list($projectId)
{
    // get intents
    $intentsClient = new IntentsClient();
    $parent = $intentsClient->projectAgentName($projectId);
    $intents = $intentsClient->listIntents($parent);

    foreach ($intents->iterateAllElements() as $intent) {
        // print relevant info
        print(str_repeat("=", 20) . PHP_EOL);
        printf('Intent name: %s' . PHP_EOL, $intent->getName());
        printf('Intent display name: %s' . PHP_EOL, $intent->getDisplayName());
        printf('Action: %s' . PHP_EOL, $intent->getAction());
        printf('Root followup intent: %s' . PHP_EOL,
            $intent->getRootFollowupIntentName());
        printf('Parent followup intent: %s' . PHP_EOL,
            $intent->getParentFollowupIntentName());
        print(PHP_EOL);

        print('Input contexts: ' . PHP_EOL);
        foreach ($intent->getInputContextNames() as $inputContextName) {
            printf("\t Name: %s" . PHP_EOL, $inputContextName);
        }

        print('Output contexts: ' . PHP_EOL);
        foreach ($intent->getOutputContexts() as $outputContext) {
            printf("\t Name: %s" . PHP_EOL, $outputContext->getName());
        }
    }
    $intentsClient->close();
}
// [END dialogflow_list_intents]
