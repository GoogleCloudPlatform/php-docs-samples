<?php

namespace Google\Cloud\Samples\Pubsub\Test;

use Google\Cloud\Samples\Pubsub\DatastoreHelper;

// @TODO: use test bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

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