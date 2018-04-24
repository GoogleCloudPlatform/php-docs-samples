<?php
/**
 * Copyright 2016 Google Inc.
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
    public function createApplication()
    {
        $app = require __DIR__ . '/../app.php';

        // set some parameters for testing
        $app['session.test'] = true;
        $app['debug'] = true;

        $app['sendgrid.sender'] = getenv('SENDGRID_SENDER');
        $app['sendgrid.api_key'] = getenv('SENDGRID_APIKEY');

        if (empty($app['sendgrid.sender']) || empty($app['sendgrid.api_key'])) {
            $this->markTestSkipped(
                'set the SENDGRID_SENDER and SENDGRID_APIKEY ' .
                'environment variables'
            );
        }

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testHome()
    {
        $client = $this->createClient();
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testSimpleEmail()
    {
        $client = $this->createClient();
        $client->request('POST', '/', ['recipient' => 'fake@example.com']);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Email sent.', $response->getContent());
    }
}
