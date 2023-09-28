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

namespace Google\Cloud\Samples\PubSub\Tests;

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\TestTrait;
use Slim\Psr7\Factory\RequestFactory;

class appTest extends TestCase
{
    use TestTrait;

    private static $app;

    public static function setUpBeforeClass(): void
    {
        // pull the app and set parameters for testing
        self::$app = require __DIR__ . '/../app.php';

        $container = self::$app->getContainer();
        $container->set('project_id', self::$projectId);
        $container->set('topic', self::requireEnv('GOOGLE_PUBSUB_TOPIC'));
        $container->set('subscription', self::requireEnv('GOOGLE_PUBSUB_SUBSCRIPTION'));
    }

    public function testInitialPage()
    {
        // make the request
        $request = (new RequestFactory)->createRequest('GET', '/');
        $response = self::$app->handle($request);

        // test the response
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testFetchMessages()
    {
        // make the request
        $request = (new RequestFactory)->createRequest('GET', '/fetch_messages');
        $response = self::$app->handle($request);

        // test the response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(is_array(json_decode($response->getBody())));
    }

    public function testSendMessage()
    {
        // make the request
        $request = (new RequestFactory)->createRequest('POST', '/send_message');
        $request->getBody()->write(http_build_query(['message' => 'foo']));
        $response = self::$app->handle($request);

        // test the response
        $this->assertEquals(204, $response->getStatusCode());
    }
}
