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

// [START dialogflow_delete_entity]
namespace Google\Cloud\Samples\Dialogflow;

use Google\Cloud\Dialogflow\V2\BatchDeleteEntitiesRequest;
use Google\Cloud\Dialogflow\V2\Client\EntityTypesClient;

/**
* Delete entity with the given entity type and entity value.
*/
function entity_delete($projectId, $entityTypeId, $entityValue)
{
    $entityTypesClient = new EntityTypesClient();

    $parent = $entityTypesClient->entityTypeName($projectId,
        $entityTypeId);
    $batchDeleteEntitiesRequest = (new BatchDeleteEntitiesRequest())
        ->setParent($parent)
        ->setEntityValues([$entityValue]);
    $entityTypesClient->batchDeleteEntities($batchDeleteEntitiesRequest);
    printf('Entity deleted: %s' . PHP_EOL, $entityValue);

    $entityTypesClient->close();
}
// [END dialogflow_delete_entity]
