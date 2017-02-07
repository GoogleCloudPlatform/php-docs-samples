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

        // skip if we are missing required env vars
        $dsn = getenv('MYSQL_DSN');
        $username = getenv('MYSQL_USERNAME');
        $password = getenv('MYSQL_PASSWORD');
        if (empty($dsn) || empty($username) || false === $password) {
            $this->markTestSkipped('set the MYSQL_DSN, MYSQL_USER and MYSQL_PASSWORD environment variables');
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

    public function testSignGuestbook()
    {
        $client = $this->createClient();

        $time = date('Y-m-d H:i:s');
        $crawler = $client->request('POST', '/', [
            'name' => 'mr Skeltal',
            'content' => sprintf('doot doot (%s)', $time),
        ]);

        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());

        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($time, $response->getContent());
    }

    public function testCreateTables()
    {
        $client = $this->createClient();

        $crawler = $client->request('get', '/create_tables');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
