<?php

/*
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Bookshelf;

use Silex\WebTestCase;

/**
 * Test for application controllers
 */
class ControllersTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../web/index.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }

    public function testTopPage()
    {
        $client = $this->createClient();
        $crawlerexport = $client->request('GET', '/');
        $resp = $client->getResponse();
        $this->assertTrue($resp->isOk());
        $this->assertContains('Hello World', $resp->getContent());
    }

    public function testBlog()
    {
        $client = $this->createClient();
        $crawlerexport = $client->request('GET', '/goodbye');
        $resp = $client->getResponse();
        $this->assertTrue($resp->isOk());
        $this->assertContains('Goodbye World', $resp->getContent());
    }
}
