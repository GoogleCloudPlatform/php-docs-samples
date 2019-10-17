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

// [START dialogflow_create_entity_type]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\EntityTypesClient;
use Google\Cloud\Dialogflow\V2\EntityType;
use Google\Cloud\Dialogflow\V2\EntityType_Kind;

/**
* Create an entity type with the given display name.
*/
function entity_type_create($projectId, $displayName, $kind = EntityType_Kind::KIND_MAP)
{
    $entityTypesClient = new EntityTypesClient();

    // prepare entity type
    $parent = $entityTypesClient->projectAgentName($projectId);
    $entityType = new EntityType();
    $entityType->setDisplayName($displayName);
    $entityType->setKind($kind);

    // create entity type
    $response = $entityTypesClient->createEntityType($parent, $entityType);
    printf('Entity type created: %s' . PHP_EOL, $response->getName());

    $entityTypesClient->close();
}
// [END dialogflow_create_entity_type]
