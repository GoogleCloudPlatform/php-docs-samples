<?php

/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace GoogleCloudPlatform\DocsSamples\Pubsub;

/**
*
*/
class DatastoreHelper
{
    public function createQuery($limit = 20)
    {
        $request = new \Google_Service_Datastore_RunQueryRequest();
        $query = new \Google_Service_Datastore_Query();

        $order = new \Google_Service_Datastore_PropertyOrder();
        $order->setDirection('descending');
        $property = new \Google_Service_Datastore_PropertyReference();
        $property->setName('created');
        $order->setProperty($property);
        $query->setOrder($order);

        $kind = new \Google_Service_Datastore_KindExpression();
        $kind->setName('PubSubMessage');
        $query->setKinds([$kind]);

        $query->setLimit($limit);

        $request->setQuery($query);

        return $request;
    }

    public function createMessageRequest(\Google_Service_Datastore_Key $id, $message)
    {
        $entity = $this->createEntity($id, $message);
        $mutation = new \Google_Service_Datastore_Mutation();
        $mutation->setUpsert([$entity]);
        $req = new \Google_Service_Datastore_CommitRequest();
        $req->setMode('NON_TRANSACTIONAL');
        $req->setMutation($mutation);

        return $req;
    }

    public function createEntity(\Google_Service_Datastore_Key $key, $message)
    {
        $entity = new \Google_Service_Datastore_Entity();
        $entity->setKey($key);
        $messageProp = new \Google_Service_Datastore_Property();
        $messageProp->setStringValue($message);
        $createdProp = new \Google_Service_Datastore_Property();
        $createdProp->setDateTimeValue(date('c'));
        $properties = [
            'message' => $messageProp,
            'created' => $createdProp,
        ];

        $entity->setProperties($properties);

        return $entity;
    }

    public function createUniqueKeyRequest()
    {
        // retrieve a unique ID from datastore
        $path = new \Google_Service_Datastore_KeyPathElement();
        $path->setKind('PubSubMessage');
        $key = new \Google_Service_Datastore_Key();
        $key->setPath([$path]);
        $idRequest = new \Google_Service_Datastore_AllocateIdsRequest();
        $idRequest->setKeys([$key]);

        return $idRequest;
    }
}