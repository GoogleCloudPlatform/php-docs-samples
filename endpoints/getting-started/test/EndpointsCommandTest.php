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

use Google\Cloud\Samples\Appengine\Endpoints\EndpointsCommand;
use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class EndpointsCommandTest extends TestCase
{
    use TestTrait;

    private $host;
    private $apiKey;
    private $message;

    public function setUp()
    {
        $host = $this->requireEnv('GOOGLE_ENDPOINTS_HOST');
        $api_key = $this->requireEnv('GOOGLE_ENDPOINTS_APIKEY');
        $this->host = $host;
        $this->apiKey = $api_key;
    }

    public function testEndpointsCommandWithNoCredentials()
    {
        $command = new EndpointsCommand();
        $tester = new CommandTester($command);
        $message = <<<EOF
So if you're lost and on your own
You can never surrender
And if your path won't lead you home
You can never surrender
EOF;
        $input = [
            'host' => $this->host,
            'api_key' => $this->apiKey,
            '--message' => $message,
        ];

        $result = $tester->execute($input);

        $this->assertEquals(0, $result);
        $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $this->assertContains(json_encode($message, $jsonFlags), $tester->getDisplay());
    }

    public function testEndpointsCommandWithApplicationCredentials()
    {
        $creds = $this->requireEnv('GOOGLE_APPLICATION_CREDENTIALS');
        $command = new EndpointsCommand();
        $tester = new CommandTester($command);
        $arguments = [
            'host' => $this->host,
            'api_key' => $this->apiKey,
            'credentials' => $creds,
        ];
        $options = [];

        $result = $tester->execute($arguments, $options);

        $this->assertEquals(0, $result);

        $credentials = json_decode(file_get_contents($creds), true);
        $this->assertContains('123456', $tester->getDisplay());
    }

    public function testEndpointsCommandWithClientSecrets()
    {
        $creds = $this->requireEnv('GOOGLE_CLIENT_SECRETS');
        $command = new EndpointsCommand();
        $tester = new CommandTester($command);
        $arguments = [
            'host' => $this->host,
            'api_key' => $this->apiKey,
            'credentials' => $creds
        ];
        $options = [];

        $result = $tester->execute($arguments, $options);

        $this->assertEquals(0, $result);

        $credentials = json_decode(file_get_contents($creds), true);
        $this->assertContains('id', $tester->getDisplay());
        $this->assertContains('email', $tester->getDisplay());
    }
}
