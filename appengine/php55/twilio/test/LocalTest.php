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

    public function testSendText()
    {
        // set your Twilio account info
        $sid = getenv('TWILIO_ACCOUNT_SID');
        $token = getenv('TWILIO_AUTH_TOKEN');
        $fromNumber = getenv('TWILIO_FROM_NUMBER');
        $toNumber = getenv('TWILIO_TO_NUMBER');

        if (empty($sid) || empty($token) || empty($fromNumber)
            || empty($toNumber)) {
            $this->markTestSkipped('set the TWILIO_ACCOUNT_SID, ' .
                'TWILIO_AUTH_TOKEN, TWILIO_FROM_NUMBER, TWILIO_TO_NUMBER ' .
                'and environment variables');
        }

        $this->app['twilio.account_sid'] = $sid;
        $this->app['twilio.auth_token'] = $token;
        $this->app['twilio.from_number'] = $fromNumber;
        $this->app['twilio.to_number'] = $toNumber;

        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testTwiml()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/twiml');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $twiml = '<Response><Say>Hello Monkey</Say></Response>';
        $this->assertContains($twiml, $response->getContent());
    }
}
