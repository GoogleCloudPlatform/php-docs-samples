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

namespace Google\Cloud\Samples\Pubsub;

/**
 * Utility class for making calls to datastore. Functions have been written
 * specifically for the PubSub sample application.
 */
class DatastoreHelper
{
    /** @var Google_Client $client */
    protected $client;

    /** @var string $datasetId */
    protected $datasetId;

    /**
     * @param $client Google API client
     * @param @datasetId the unique ID for this dataset, for this
     * example we use the project ID
     */
    public function __construct(\Google_Client $client, $datasetId)
    {
        $this->client = $client;
        $this->datasetId = $datasetId;
    }

    /**
     * Creates a query object for pulling the last $limit number of
     * "PubSubMessage" items, equivalent to the following GQL:
     *
     *    SELECT * from PubSubMessage ORDER BY created DESC LIMIT 20
     *
     * @see https://cloud.google.com/datastore/docs/concepts/gql
     */
    public function createQuery($limit = 20)
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
        $kind->setName('PubSubMessage');
        $query->setKinds([$kind]);

        $query->setLimit($limit);

        $request->setQuery($query);

        return $request;
    }

    /**
     * Creates the request to store a PubSubMessage item in datastore
     */
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

    /**
     * Creates the basic entity for PubSubMessage, with properties "created"
     * and "message"
     */
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

    /**
     * Fetches a unique key from Datastore for a PubSub message
     */
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

    /**
     * Queries the Datastore API for all messages and returns as an array
     *
     * @see createQuery
     * @return array
     */
    public function fetchMessages()
    {
        $datastore = new \Google_Service_Datastore($this->client);

        $query = $this->createQuery();

        $response = $datastore->datasets->runQuery($this->datasetId, $query);

        $messages = [];
        foreach ($response->getBatch()->getEntityResults() as $entityResult) {
            $properties = $entityResult->getEntity()->getProperties();
            $messages[] = $properties['message']->getStringValue();
        }

        return $messages;
    }

    /**
     * Stores a single message in Datastore using the Datastore API
     *
     * @param string $message
     * @return bool
     */
    public function storeMessage($message)
    {
        $datastore = new \Google_Service_Datastore($this->client);

        $idRequest = $this->createUniqueKeyRequest();
        $uniqueId = $datastore->datasets->allocateIds($this->datasetId, $idRequest);
        $key = $uniqueId->getKeys()[0];

        // build the API request to send using the key and message
        $request = $this->createMessageRequest($key, $message);

        return $datastore->datasets->commit($this->datasetId, $request);
    }
}
