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
use Silex\WebTestCase;

class datastoreTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../src/app.php';

        // set some parameters for testing
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

    public function testHome()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testStore()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/store', [
            'name' => 'test-comment',
            'body' => 'body of comment'
        ]);

        $response = $client->getResponse();
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/', $response->headers->get('location'));
    }

    public function testStoreMissingParameters()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/store');

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }
}
