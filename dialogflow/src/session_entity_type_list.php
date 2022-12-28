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

// [START dialogflow_list_session_entity_types]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\SessionEntityTypesClient;

function session_entity_type_list($projectId, $sessionId)
{
    $sessionEntityTypesClient = new SessionEntityTypesClient();
    $parent = $sessionEntityTypesClient->sessionName($projectId, $sessionId);
    $sessionEntityTypes = $sessionEntityTypesClient->listSessionEntityTypes($parent);
    print('Session entity types:' . PHP_EOL);
    foreach ($sessionEntityTypes->iterateAllElements() as $sessionEntityType) {
        printf('Session entity type name: %s' . PHP_EOL, $sessionEntityType->getName());
        printf('Number of entities: %d' . PHP_EOL, count($sessionEntityType->getEntities()));
    }
    $sessionEntityTypesClient->close();
}
// [END dialogflow_list_session_entity_types]
