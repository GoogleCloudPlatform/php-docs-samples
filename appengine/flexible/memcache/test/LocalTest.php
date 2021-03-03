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

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\TestTrait;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\UriFactory;
use Slim\Psr7\Request;
use GeckoPackages\MemcacheMock\MemcachedMock;

class LocalTest extends TestCase
{
    private static $app;

    public static function setUpBeforeClass(): void
    {
        $app = require __DIR__ . '/../app.php';
        $container = $app->getContainer();
        $memcached = new MemcachedMock;
        $memcached->addServer("localhost", 11211);
        $container->set('memcached', $memcached);
        self::$app = $app;
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $request = (new RequestFactory)->createRequest('GET', '/');
        $response = self::$app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());

        // Make sure it handles a POST request too, which will increment the
        // counter.
        $response = $this->createRequest('POST', '/');
        $response = self::$app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAndPut()
    {
        // Use a random key to avoid colliding with simultaneous tests.
        $key = rand(0, 1000);

        // Test the /memcached REST API.
        $request = $this->createRequest('PUT', "/memcached/test$key", "sour");
        $response = self::$app->handle($request);
        $this->assertEquals(200, (string) $response->getStatusCode());
        $request = (new RequestFactory)->createRequest('GET', "/memcached/test$key");
        $response = self::$app->handle($request);
        $this->assertEquals("sour", (string) $response->getBody());


        $request = $this->createRequest('PUT', "/memcached/test$key", "sweet");
        $response = self::$app->handle($request);
        $this->assertEquals(200, (string) $response->getStatusCode());
        $request = (new RequestFactory)->createRequest('GET', "/memcached/test$key");
        $response = self::$app->handle($request);
        $this->assertEquals("sweet", (string) $response->getBody());

    }

    /**
     * HTTP PUTs the body to the url path.
     * @param $path string
     * @param $body string
     */
    private function createRequest($method, $path, $body = '')
    {
        return new Request(
            'PUT',
            (new UriFactory)->createUri($path),
            new \Slim\Psr7\Headers(),
            [],
            [],
            (new StreamFactory)->createStream($body)
        );
    }
}
