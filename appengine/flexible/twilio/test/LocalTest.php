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
        self::$app = require __DIR__ . '/../app.php';

        // set your Twilio API key and secret
        self::requireEnv('TWILIO_ACCOUNT_SID');
        self::requireEnv('TWILIO_AUTH_TOKEN');
    }

    public function testReceiveCall()
    {
        $request = (new RequestFactory)->createRequest('POST', '/call/receive');
        $response = self::$app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            '<Say>Hello from Twilio!</Say>',
            (string) $response->getBody()
        );
    }

    public function testReceiveSms()
    {
        $params = [
            'From' => '16505551212',
            'Body' => 'This is the best text message ever sent.'
        ];
        $request = (new RequestFactory)->createRequest('POST', '/sms/receive');
        $request->getBody()->write(http_build_query($params));
        $response = self::$app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString($params['From'], $response->getBody());
        $this->assertStringContainsString($params['Body'], $response->getBody());
    }

    public function testSendSms()
    {
        $params = [
            'to' => '16505551212',
        ];
        $request = (new RequestFactory)->createRequest('POST', '/sms/send');
        $request->getBody()->write(http_build_query($params));
        $response = self::$app->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Hello from Twilio!', $response->getBody());
    }
}
