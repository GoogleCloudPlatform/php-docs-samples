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
use Google\Cloud\TestUtils\TestTrait;
use Slim\Psr7\Factory\RequestFactory;

class LocalTest extends TestCase
{
    use TestTrait;

    private static $app;

    public static function setUpBeforeClass(): void
    {
        self::requireEnv('GOOGLE_STORAGE_BUCKET');

        $app = require __DIR__ . '/../app.php';

        // the bucket name doesn't matter because we mock the stream wrapper
        $container = $app->getContainer();
        $container->set('project_id', getenv('GOOGLE_PROJECT_ID'));
        $container->set('bucket_name', getenv('GOOGLE_STORAGE_BUCKET'));
        $container->set('object_name', 'appengine/hello_flex.txt');

        self::$app = $app;
    }

    public function testHome()
    {
        $request = (new RequestFactory)->createRequest('GET', '/');
        $response = self::$app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWrite()
    {
        $time = date('Y-m-d H:i:s');
        $request = (new RequestFactory)->createRequest('POST', '/write');
        $request->getBody()->write(http_build_query([
            'content' => sprintf('doot doot (%s)', $time),
        ]));

        $response = self::$app->handle($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/', $response->getHeaderLine('Location'));

        $request = (new RequestFactory)->createRequest('GET', '/');
        $response = self::$app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString($time, (string) $response->getBody());
    }
}
