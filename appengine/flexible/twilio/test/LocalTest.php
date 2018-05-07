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
        $projectId = getenv('GOOGLE_PROJECT_ID');

        // set your Mailjet API key and secret
        $app['twilio.account_sid'] = getenv('TWILIO_ACCOUNT_SID');
        $app['twilio.auth_token']  = getenv('TWILIO_AUTH_TOKEN');
        $app['twilio.number'] = getenv('TWILIO_FROM_NUMBER');

        if (empty($app['twilio.account_sid']) ||
            empty($app['twilio.auth_token'])) {
            $this->markTestSkipped(
                'set the TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN ' .
                'environment variables');
        }

        // prevent HTML error exceptions
        unset($app['exception_handler']);

        return $app;
    }

    public function testReceiveCall()
    {
        $client = $this->createClient();

        $crawler = $client->request('POST', '/call/receive');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains(
            '<Say>Hello from Twilio!</Say>',
            $response->getContent()
        );
    }

    public function testReceiveSms()
    {
        $client = $this->createClient();
        $params = [
            'From' => '16505551212',
            'Body' => 'This is the best text message ever sent.'
        ];
        $crawler = $client->request('POST', '/sms/receive', $params);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains($params['From'], $response->getContent());
        $this->assertContains($params['Body'], $response->getContent());
    }

    public function testSendSms()
    {
        $client = $this->createClient();
        $params = [
            'to' => '16505551212',
        ];
        $crawler = $client->request('POST', '/sms/send', $params);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Hello from Twilio!', $response->getContent());
    }
}
