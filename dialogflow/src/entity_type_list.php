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

// [START dialogflow_list_entity_types]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\EntityTypesClient;

function entity_type_list($projectId)
{
    // get entity types
    $entityTypesClient = new EntityTypesClient();
    $parent = $entityTypesClient->projectAgentName($projectId);
    $entityTypes = $entityTypesClient->listEntityTypes($parent);

    foreach ($entityTypes->iterateAllElements() as $entityType) {
        // print relevant info
        printf('Entity type name: %s' . PHP_EOL, $entityType->getName());
        printf('Entity type display name: %s' . PHP_EOL, $entityType->getDisplayName());
        printf('Number of entities: %d' . PHP_EOL, count($entityType->getEntities()));
    }

    $entityTypesClient->close();
}
// [END dialogflow_list_entity_types]
