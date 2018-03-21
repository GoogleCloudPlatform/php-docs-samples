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

// [START dialogflow_create_entity]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\EntityTypesClient;
use Google\Cloud\Dialogflow\V2\EntityType_Entity;

/**
* Create an entity of the given entity type.
*/
function entity_create($projectId, $entityTypeId, $entityValue, $synonyms = [])
{
    // synonyms must be exactly [$entityValue] if the entityTypes'
    // kind is KIND_LIST
    if (!$synonyms) {
        $synonyms = [$entityValue];
    }

    $entityTypesClient = new EntityTypesClient();
    $parent = $entityTypesClient->entityTypeName($projectId,
        $entityTypeId);

    // prepare entity
    $entity = new EntityType_Entity();
    $entity->setValue($entityValue);
    $entity->setSynonyms($synonyms);

    // create entity
    $response = $entityTypesClient->batchCreateEntities($parent, [$entity]);
    printf('Entity created: %s' . PHP_EOL, $response->getName());

    $entityTypesClient->close();
}
// [END dialogflow_create_entity]
