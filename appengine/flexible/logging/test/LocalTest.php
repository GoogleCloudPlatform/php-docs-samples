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

class LocalTest extends WebTestCase
{
    public function setUp()
    {
        if (!getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Must set GOOGLE_PROJECT_ID');
        }
        parent::setUp();
        $this->client = $this->createClient();
    }

    public function createApplication()
    {
        $app = require __DIR__ . '/../app.php';
        $app['project_id'] = getenv('GOOGLE_PROJECT_ID');
        return $app;
    }

    public function testSomeLogs()
    {
        $this->client->request('GET', '/');
        $response = $this->client->getResponse();
        $this->assertTrue($response->isOk());
        $text = $response->getContent();
        $this->assertContains("Logs:", $text);
    }

    public function testAsyncLog()
    {
        $this->client->request('GET', '/async_log');
        $response = $this->client->getResponse();
        $this->assertTrue($response->isOk());
    }
}
