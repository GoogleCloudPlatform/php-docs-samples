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

// [START dialogflow_create_session_entity_type]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\Client\SessionEntityTypesClient;
use Google\Cloud\Dialogflow\V2\CreateSessionEntityTypeRequest;
use Google\Cloud\Dialogflow\V2\EntityType\Entity;
use Google\Cloud\Dialogflow\V2\SessionEntityType;
use Google\Cloud\Dialogflow\V2\SessionEntityType\EntityOverrideMode;

/**
* Create a session entity type with the given display name.
*/
function session_entity_type_create($projectId, $displayName, $values,
    $sessionId, $overrideMode = EntityOverrideMode::ENTITY_OVERRIDE_MODE_OVERRIDE)
{
    $sessionEntityTypesClient = new SessionEntityTypesClient();
    $parent = $sessionEntityTypesClient->sessionName($projectId, $sessionId);

    // prepare name
    $sessionEntityTypeName = $sessionEntityTypesClient
        ->sessionEntityTypeName($projectId, $sessionId, $displayName);

    // prepare entities
    $entities = [];
    foreach ($values as $value) {
        $entity = (new Entity())
            ->setValue($value)
            ->setSynonyms([$value]);
        $entities[] = $entity;
    }

    // prepare session entity type
    $sessionEntityType = (new SessionEntityType())
        ->setName($sessionEntityTypeName)
        ->setEntityOverrideMode($overrideMode)
        ->setEntities($entities);

    // create session entity type
    $createSessionEntityTypeRequest = (new CreateSessionEntityTypeRequest())
        ->setParent($parent)
        ->setSessionEntityType($sessionEntityType);
    $response = $sessionEntityTypesClient->createSessionEntityType($createSessionEntityTypeRequest);
    printf('Session entity type created: %s' . PHP_EOL, $response->getName());

    $sessionEntityTypesClient->close();
}
// [END dialogflow_create_session_entity_type]
