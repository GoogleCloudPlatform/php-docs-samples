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

use Silex\WebTestCase;
use Symfony\Component\HttpKernel\Client;
use GuzzleHttp\Client as HttpClient;

// @TODO: use test bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

class pubsubTest extends WebTestCase
{
    public function createApplication()
    {
        // pull the app and set parameters for testing
        $app = require __DIR__ . '/../src/app.php';

        $app['session.test'] = true;
        $app['debug'] = true;
        $app['project_id'] = 'cloud-samples-tests-php';

        // this will be set by travis, but may not be set locally
        if (!$credentials = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $credentials = __DIR__ . '/../../credentials.json';
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);
        }

        if (!file_exists($credentials) || 0 == filesize($credentials)) {
            $this->markTestSkipped('credentials not found');
        }

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testInitialPage()
    {
        // create the application
        $client = $this->createClient();

        // make the request
        $crawler = $client->request('GET', '/');

        // test the response
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testFetchMessages()
    {
        // set up mock objects
        $apiResponse = new \Google_Service_Datastore_RunQueryResponse();
        $apiResponse->setBatch(new \Google_Service_Datastore_QueryResultBatch());
        $apiClient = $this->getMock('Google_Client');
        $apiClient
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue($apiResponse));
        $apiClient
            ->expects($this->once())
            ->method('getLogger')
            ->will($this->returnValue($this->getMock('Psr\Log\LoggerInterface')));
        $apiClient
            ->expects($this->once())
            ->method('getHttpClient')
            ->will($this->returnValue(new HttpClient()));

        // create the application
        $app = $this->createApplication();
        $app['google_client'] = $apiClient;
        $client = new Client($app);

        // make the request
        $crawler = $client->request('GET', '/fetch_messages');

        // test the response
        $response = $client->getResponse();
        $this->assertTrue($response->isOk());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertEquals('[]', $response->getContent());
    }

    public function testSendMessage()
    {
        // set up mock objects
        $apiClient = $this->getMock('Google_Client');
        $apiClient
            ->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));
        $apiClient
            ->expects($this->once())
            ->method('getLogger')
            ->will($this->returnValue($this->getMock('Psr\Log\LoggerInterface')));
        $apiClient
            ->expects($this->once())
            ->method('getHttpClient')
            ->will($this->returnValue(new HttpClient()));

        // create the application
        $app = $this->createApplication();
        $app['google_client'] = $apiClient;
        $client = new Client($app);

        // make the request
        $crawler = $client->request('POST', '/send_message', ['message' => 'foo']);

        // test the response
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
    }
}
