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
use Slim\Psr7\Factory\RequestFactory;
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
        $request1 = (new RequestFactory)->createRequest('GET', '/');
        $response = self::$app->handle($request1);
        $this->assertEquals(200, $response->getStatusCode());

        // Make sure it handles a POST request too, which will increment the
        // counter.
        $request2 = (new RequestFactory)->createRequest('POST', '/');
        $response = self::$app->handle($request2);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAndPut()
    {
        // Use a random key to avoid colliding with simultaneous tests.
        $key = rand(0, 1000);

        // Test the /memcached REST API.
        $request1 = (new RequestFactory)->createRequest('PUT', "/memcached/test$key");
        $request1->getBody()->write('sour');
        $response1 = self::$app->handle($request1);
        $this->assertEquals(200, (string) $response1->getStatusCode());

        // Check that the key was written as expected
        $request2 = (new RequestFactory)->createRequest('GET', "/memcached/test$key");
        $response2 = self::$app->handle($request2);
        $this->assertEquals("sour", (string) $response2->getBody());

        // Test the /memcached REST API with a new value.
        $request3 = (new RequestFactory)->createRequest('PUT', "/memcached/test$key");
        $request3->getBody()->write('sweet');
        $response3 = self::$app->handle($request3);
        $this->assertEquals(200, (string) $response3->getStatusCode());

        // Check that the key was written as expected
        $request4 = (new RequestFactory)->createRequest('GET', "/memcached/test$key");
        $response4 = self::$app->handle($request4);
        $this->assertEquals("sweet", (string) $response4->getBody());
    }
}
