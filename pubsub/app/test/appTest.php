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
namespace Google\Cloud\Samples\PubSub\Tests;

use Silex\WebTestCase;
use Symfony\Component\HttpKernel\Client;

class appTest extends WebTestCase
{
    public function createApplication()
    {
        // pull the app and set parameters for testing
        $app = require __DIR__ . '/../app.php';

        $app['session.test'] = true;
        $app['debug'] = true;
        $app['project_id'] = 'cloud-samples-tests-php';
        $app['topic'] = getenv('GOOGLE_PUBSUB_TOPIC');
        $app['subscription'] = getenv('GOOGLE_PUBSUB_SUBSCRIPTION');

        // this will be set by travis, but may not be set locally
        if (!$credentials = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $credentials = __DIR__ . '/../../credentials.json';
            putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentials);
        }

        if (!file_exists($credentials) || 0 == filesize($credentials)) {
            $this->markTestSkipped('credentials not found');
        }
        if (empty($app['topic']) || empty($app['subscription'])) {
            $this->markTestSkipped('topic or subscription not set');
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
        // create the application
        $app = $this->createApplication();
        $client = new Client($app);

        // make the request
        $crawler = $client->request('GET', '/fetch_messages');

        // test the response
        $response = $client->getResponse();
        $this->assertTrue($response->isOk());
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);
        $this->assertTrue(is_array(json_decode($response->getContent())));
    }

    public function testSendMessage()
    {   // create the application
        $app = $this->createApplication();
        $client = new Client($app);

        // make the request
        $crawler = $client->request('POST', '/send_message', ['message' => 'foo']);

        // test the response
        $response = $client->getResponse();
        $this->assertEquals(204, $response->getStatusCode());
    }
}
