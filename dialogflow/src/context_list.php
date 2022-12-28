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

// [START dialogflow_list_contexts]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\ContextsClient;

function context_list($projectId, $sessionId)
{
    // get contexts
    $contextsClient = new ContextsClient();
    $parent = $contextsClient->sessionName($projectId, $sessionId);
    $contexts = $contextsClient->listContexts($parent);

    printf('Contexts for session %s' . PHP_EOL, $parent);
    foreach ($contexts->iterateAllElements() as $context) {
        // print relevant info
        printf('Context name: %s' . PHP_EOL, $context->getName());
        printf('Lifespan count: %d' . PHP_EOL, $context->getLifespanCount());
    }

    $contextsClient->close();
}
// [END dialogflow_list_contexts]
