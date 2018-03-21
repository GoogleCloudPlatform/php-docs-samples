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

// [START dialogflow_delete_entity_type]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\EntityTypesClient;

function entity_type_delete($projectId, $entityTypeId)
{
    $entityTypesClient = new EntityTypesClient();

    $formattedName = $entityTypesClient->entityTypeName($projectId, 
        $entityTypeId);
    $entityTypesClient->deleteEntityType($formattedName);
    printf('Entity type deleted: %s' . PHP_EOL, $formattedName);
    
    $entityTypesClient->close();

}
// [END dialogflow_delete_entity_type]
