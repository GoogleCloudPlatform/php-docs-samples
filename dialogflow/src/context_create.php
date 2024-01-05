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

// [START dialogflow_create_context]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\Client\ContextsClient;
use Google\Cloud\Dialogflow\V2\Context;
use Google\Cloud\Dialogflow\V2\CreateContextRequest;

function context_create($projectId, $contextId, $sessionId, $lifespan = 1)
{
    $contextsClient = new ContextsClient();

    // prepare context
    $parent = $contextsClient->sessionName($projectId, $sessionId);
    $contextName = $contextsClient->contextName($projectId, $sessionId, $contextId);
    $context = new Context();
    $context->setName($contextName);
    $context->setLifespanCount($lifespan);

    // create context
    $createContextRequest = (new CreateContextRequest())
        ->setParent($parent)
        ->setContext($context);
    $response = $contextsClient->createContext($createContextRequest);
    printf('Context created: %s' . PHP_EOL, $response->getName());

    $contextsClient->close();
}
// [END dialogflow_create_context]
