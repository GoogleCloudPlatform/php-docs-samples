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

namespace Google\Cloud\Samples\Datastore;

/**
 * Utility class for making calls to datastore. Functions have been written
 * specifically for the PubSub sample application.
 */
class DatastoreHelper
{
    const KIND = 'DatastoreComment';

    /**
     * Creates a query object for pulling the last $limit number of
     * "PubSubMessage" items, equivalent to the following GQL:
     *
     *    SELECT * from DatastoreComment ORDER BY created DESC LIMIT 20
     *
     * @see https://cloud.google.com/datastore/docs/concepts/gql
     */
    public function createSimpleQuery($limit = 20)
    {
        $request = new \Google_Service_Datastore_RunQueryRequest();
        $query = new \Google_Service_Datastore_Query();

        $order = new \Google_Service_Datastore_PropertyOrder();
        $order->setDirection('descending');
        $property = new \Google_Service_Datastore_PropertyReference();
        $property->setName('created');
        $order->setProperty($property);
        $query->setOrder([$order]);

        $kind = new \Google_Service_Datastore_KindExpression();
        $kind->setName(self::KIND);
        $query->setKinds([$kind]);

        $query->setLimit($limit);

        $request->setQuery($query);

        return $request;
    }

    /**
     * Creates the request to store a DatastoreComment item in datastore
     */
    public function createCommentRequest(\Google_Service_Datastore_Key $id, $name, $body)
    {
        $entity = $this->createEntity($id, $name, $body);
        $mutation = new \Google_Service_Datastore_Mutation();
        $mutation->setUpsert([$entity]);
        $req = new \Google_Service_Datastore_CommitRequest();
        $req->setMode('NON_TRANSACTIONAL');
        $req->setMutation($mutation);

        return $req;
    }

    /**
     * Creates the basic entity for DatastoreComment, with properties "created"
     * "name", and "body"
     */
    public function createEntity(\Google_Service_Datastore_Key $key, $name, $body)
    {
        $entity = new \Google_Service_Datastore_Entity();
        $entity->setKey($key);
        $nameProp = new \Google_Service_Datastore_Property();
        $nameProp->setStringValue($name);
        $bodyProp = new \Google_Service_Datastore_Property();
        $bodyProp->setStringValue($body);
        $createdProp = new \Google_Service_Datastore_Property();
        $createdProp->setDateTimeValue(date('c'));
        $properties = [
            'name'    => $nameProp,
            'body' => $bodyProp,
            'created' => $createdProp,
        ];

        $entity->setProperties($properties);

        return $entity;
    }

    /**
     * Fetches a unique key from Datastore
     */
    public function createUniqueKeyRequest()
    {
        // retrieve a unique ID from datastore
        $path = new \Google_Service_Datastore_KeyPathElement();
        $path->setKind(self::KIND);
        $key = new \Google_Service_Datastore_Key();
        $key->setPath([$path]);
        $idRequest = new \Google_Service_Datastore_AllocateIdsRequest();
        $idRequest->setKeys([$key]);

        return $idRequest;
    }
}
