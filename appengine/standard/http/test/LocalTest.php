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

    public function testFileRequest()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/request/file');

        $this->assertTrue($client->getResponse()->isOk());

        // test the regex exists
        $this->assertResponse((string) $client->getResponse());
    }

    public function testCurlRequest()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/request/curl');

        $this->assertTrue($client->getResponse()->isOk());

        // test the regex exists
        $this->assertResponse((string) $client->getResponse());
    }

    public function testGuzzleRequest()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/request/curl');

        $this->assertTrue($client->getResponse()->isOk());

        // test the regex exists
        $this->assertResponse((string) $client->getResponse());
    }

    private function assertResponse($response)
    {
        $regex = '/<pre>(.*)<\/pre>/m';
        $response = preg_replace("/\r|\n/", '', $response);
        $this->assertRegExp($regex, $response);
        preg_match($regex, html_entity_decode($response), $matches);

        $json = json_decode($matches[1], true);
        $this->assertArrayHasKey('args', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('files', $json);
        $this->assertArrayHasKey('headers', $json);
        $this->assertArrayHasKey('json', $json);
        $this->assertArrayHasKey('origin', $json);
        $this->assertArrayHasKey('url', $json);

        $this->assertArrayHasKey('Custom-Header', $json['headers']);
        $this->assertEquals('custom-value', $json['headers']['Custom-Header']);
    }
}
