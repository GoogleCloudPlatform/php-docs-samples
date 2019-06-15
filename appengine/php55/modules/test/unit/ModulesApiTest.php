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
use google\appengine\api\modules\ModulesService;
use Silex\WebTestCase;

require_once __DIR__ . '/mocks/Functions.php';
require_once __DIR__ . '/mocks/ModulesService.php';

class ModulesApiTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../app.php';

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testTopPage()
    {
        // Set module and instance
        $module = 'my-module';
        $instance = 'my-instance';
        ModulesService::$module = $module;
        ModulesService::$instance = $instance;
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains(
            $module . ':' . $instance,
            $client->getResponse()->getContent());
    }

    public function testAccessBackend()
    {
        // Set hostname
        $hostname = 'myhost.example.com';
        ModulesService::$hostname = $hostname;
        $client = $this->createClient();

        $crawler = $client->request('GET', '/access_backend');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains($hostname, $client->getResponse()->getContent());
    }
}
