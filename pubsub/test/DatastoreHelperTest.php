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
namespace Google\Cloud\Samples\pubsub\test;

use Google\Cloud\Samples\Pubsub\DatastoreHelper;

class DatastoreHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateQuery()
    {
        $client = $this->getMock('Google_Client');
        $datastore = new DatastoreHelper($client, 'test-dataset-id');

        $request = $datastore->createQuery(14);

        $this->assertInstanceOf('Google_Service_Datastore_RunQueryRequest', $request);
        $this->assertEquals(14, $request->getQuery()->getLimit());
    }

    public function testCreateEntity()
    {
        $client = $this->getMock('Google_Client');
        $datastore = new DatastoreHelper($client, 'test-dataset-id');

        $key = $this->getMock('Google_Service_Datastore_Key');
        $entity = $datastore->createEntity($key, 'my test message');

        $this->assertInstanceOf('Google_Service_Datastore_Entity', $entity);

        $properties = $entity->getProperties();
        $this->assertArrayHasKey('message', $properties);

        $properties = $entity->getProperties();
        $this->assertEquals('my test message', $properties['message']->getStringValue());
    }

    public function testCreateUniqueKeyRequest()
    {
        $client = $this->getMock('Google_Client');
        $datastore = new DatastoreHelper($client, 'test-dataset-id');

        $key = $datastore->createUniqueKeyRequest();

        $this->assertInstanceOf('Google_Service_Datastore_AllocateIdsRequest', $key);
    }
}
