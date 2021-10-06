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

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Prophecy\Argument;

class LocalTest extends TestCase
{
    public function testIndex()
    {
        $app = require __DIR__ . '/../app.php';

        $memcached = $this->prophesize(Memcached::class);
        $container = $app->getContainer();
        $container->set('memcached', $memcached->reveal());

        // Access the modules app top page.
        $request1 = (new RequestFactory)->createRequest('GET', '/');
        $response = $app->handle($request1);
        $this->assertEquals(200, $response->getStatusCode());

        // Make sure it handles a POST request too, which will increment the
        // counter.
        $request2 = (new RequestFactory)->createRequest('POST', '/');
        $response = $app->handle($request2);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAndPut()
    {
        $app = require __DIR__ . '/../app.php';

        $memcached = $this->prophesize(Memcached::class);
        $memcached->set('testkey1', 'sour', Argument::type('int'))
            ->willReturn(true);
        $memcached->get('testkey1')
            ->willReturn('sour');

        $memcached->set('testkey2', 'sweet', Argument::type('int'))
            ->willReturn(true);
        $memcached->get('testkey2')
            ->willReturn('sweet');

        $container = $app->getContainer();
        $container->set('memcached', $memcached->reveal());

        // Use a random key to avoid colliding with simultaneous tests.

        // Test the /memcached REST API.
        $request1 = (new RequestFactory)->createRequest('PUT', '/memcached/testkey1');
        $request1->getBody()->write('sour');
        $response1 = $app->handle($request1);
        $this->assertEquals(200, (string) $response1->getStatusCode());

        // Check that the key was written as expected
        $request2 = (new RequestFactory)->createRequest('GET', '/memcached/testkey1');
        $response2 = $app->handle($request2);
        $this->assertEquals('sour', (string) $response2->getBody());

        // Test the /memcached REST API with a new value.
        $request3 = (new RequestFactory)->createRequest('PUT', '/memcached/testkey2');
        $request3->getBody()->write('sweet');
        $response3 = $app->handle($request3);
        $this->assertEquals(200, (string) $response3->getStatusCode());

        // Check that the key was written as expected
        $request4 = (new RequestFactory)->createRequest('GET', '/memcached/testkey2');
        $response4 = $app->handle($request4);
        $this->assertEquals('sweet', (string) $response4->getBody());
    }
}

if (!class_exists('Memcached')) {
    interface Memcached
    {
        public function get($key);
        public function set($key, $value, $timestamp = 0);
        public function increment();
    }
}
