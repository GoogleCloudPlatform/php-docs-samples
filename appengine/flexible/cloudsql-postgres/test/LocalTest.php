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
        parent::setUp();
        $this->client = $this->createClient();
    }

    public function createApplication()
    {
        if (!getenv('POSTGRES_DSN') ||
            !getenv('POSTGRES_USER') ||
            !getenv('POSTGRES_PASSWORD')) {
            $this->markTestSkipped('set the POSTGRES_DSN, POSTGRES_USER and POSTGRES_PASSWORD environment variables');
            return;
        }
        $app = require __DIR__ . '/../app.php';
        return $app;
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $client = $this->client;
        $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
        $text = $client->getResponse()->getContent();
        $this->assertContains("Last 10 visits:", $text);
    }
}
