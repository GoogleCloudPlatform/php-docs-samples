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
namespace Google\Cloud\Test;

use Silex\WebTestCase;
use GeckoPackages\MemcacheMock\MemcachedMock;

class LocalTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->client = $this->createClient();
    }

    public function createApplication()
    {
        $app = require __DIR__ . '/../app.php';
        $app['memcached'] = new MemcachedMock;
        $app['memcached']->addServer("localhost", 11211);
        return $app;
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $client = $this->client;
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());

        // Make sure it handles a POST request too, which will increment the
        // counter.
        $this->client->request('POST', '/');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

    public function testGetAndPut()
    {
        // Use a random key to avoid colliding with simultaneous tests.
        $key = rand(0, 1000);

        // Test the /memcached REST API.
        $this->put("/memcached/test$key", "sour");
        $this->assertEquals("sour", $this->get("/memcached/test$key"));
        $this->put("/memcached/test$key", "sweet");
        $this->assertEquals("sweet", $this->get("/memcached/test$key"));
    }

    /**
     * HTTP PUTs the body to the url path.
     * @param $path string
     * @param $body string
     */
    private function put($path, $body)
    {
        $this->client->request('PUT', $path, array(), array(), array(), $body);
        return $this->client->getResponse()->getContent();
    }

    /**
     * HTTP GETs the url path.
     * @param $path string
     * @return string The HTTP Response.
     */
    private function get($path)
    {
        $this->client->request('GET', $path);
        return $this->client->getResponse()->getContent();
    }
}
