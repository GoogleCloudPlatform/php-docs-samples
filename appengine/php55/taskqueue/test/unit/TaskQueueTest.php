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

use google\appengine\api\taskqueue\PushTask;
use Silex\WebTestCase;

require_once __DIR__ . '/mocks/PushTask.php';
require_once __DIR__ . '/mocks/PushQueue.php';

class TaskQueueTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../app.php';
        // prevent HTML error exceptions
        unset($app['exception_handler']);
        // Reset our mock
        PushTask::reset();
        return $app;
    }

    public function testTopPage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains(
            'A task task0 added.',
            $client->getResponse()->getContent());
        $this->assertEquals(4, count(PushTask::$tasks));
        $this->assertEquals(4, count(PushTask::$added));
        foreach (PushTask::$added as $added) {
            $this->assertTrue($added);
        }
    }

    public function testWorker()
    {
        $client = $this->createClient();
        $crawler = $client->request('POST', '/worker');
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testSomeUrl()
    {
        $client = $this->createClient();
        $crawler = $client->request('POST', '/someUrl');
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testSomeOtherUrl()
    {
        $client = $this->createClient();
        $crawler = $client->request('POST', '/someOtherUrl');
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testMyWorker()
    {
        $client = $this->createClient();
        $crawler = $client->request('POST', '/path/to/my/worker');
        $this->assertTrue($client->getResponse()->isOk());
    }
}
