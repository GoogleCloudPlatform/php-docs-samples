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

use Google\Cloud\Dialogflow\V2\SessionEntityType_EntityOverrideMode;
use Google\Cloud\Dialogflow\V2\SessionEntityTypesClient;
use Google\Cloud\Dialogflow\V2\SessionEntityType;
use Google\Cloud\Dialogflow\V2\EntityType_Entity;

/**
* Create a session entity type with the given display name.
*/
function session_entity_type_create($projectId, $displayName, $values,
    $sessionId, $overrideMode = SessionEntityType_EntityOverrideMode::ENTITY_OVERRIDE_MODE_OVERRIDE)
{
    $sessionEntityTypesClient = new SessionEntityTypesClient();
    $parent = $sessionEntityTypesClient->sessionName($projectId, $sessionId);

    // prepare name
    $sessionEntityTypeName = $sessionEntityTypesClient
    ->sessionEntityTypeName($projectId, $sessionId, $displayName);

    // prepare entities
    $entities = [];
    foreach ($values as $value) {
        $entity = new EntityType_Entity();
        $entity->setValue($value);
        $entity->setSynonyms([$value]);
        $entities[] = $entity;
    }

    // prepare session entity type
    $sessionEntityType = new SessionEntityType();
    $sessionEntityType->setName($sessionEntityTypeName);
    $sessionEntityType->setEntityOverrideMode($overrideMode);
    $sessionEntityType->setEntities($entities);

    // create session entity type
    $response = $sessionEntityTypesClient->createSessionEntityType($parent,
        $sessionEntityType);
    printf('Session entity type created: %s' . PHP_EOL, $response->getName());

    $sessionEntityTypesClient->close();
}
// [END dialogflow_create_session_entity_type]
