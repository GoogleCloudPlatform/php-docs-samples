<?php
/**
 * Copyright 2017 Google Inc.
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
use Silex\WebTestCase;

class LocalTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->client = $this->createClient();
    }

    public function createApplication()
    {
        $app = require __DIR__ . '/../index.php';

        // set some parameters for testing
        $app['session.test'] = true;
        $app['debug'] = true;

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testHome()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $response = $client->getResponse();
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('Hello World', $response->getContent());
    }

    public function testLogPayload()
    {
        $client = $this->createClient();
        $crawler = $client->request('POST', '/example_task_handler', [], [], [], 'google');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains(sprintf('Received task with payload: google'), $response->getContent());
    }
}
