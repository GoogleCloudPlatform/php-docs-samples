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
namespace Google\Cloud\Samples\Endpoints;

use Google\Cloud\Samples\Appengine\Endpoints\EndpointsCommand;
use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

class endpointsTest extends TestCase
{
    use TestTrait;

    private $host;
    private $apiKey;
    private $message;

    public function setUp(): void
    {
        $host = $this->requireEnv('GOOGLE_ENDPOINTS_HOST');
        $api_key = $this->requireEnv('GOOGLE_ENDPOINTS_APIKEY');
        $this->host = $host;
        $this->apiKey = $api_key;
    }

    public function testEndpointWithNoCredentials()
    {
        $message = <<<EOF
So if you're lost and on your own
You can never surrender
And if your path won't lead you home
You can never surrender
EOF;
        $output = $this->runFunctionSnippet('make_request', [
            'host' => $this->host,
            'api_key' => $this->apiKey,
            'credentials' => '',
            'message' => $message,
        ]);
        $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        $this->assertStringContainsString(json_encode($message, $jsonFlags), $output);
    }

    public function testEndpointsCommandWithApplicationCredentials()
    {
        $creds = $this->requireEnv('GOOGLE_APPLICATION_CREDENTIALS');

        $output = $this->runFunctionSnippet('make_request', [
            'host' => $this->host,
            'api_key' => $this->apiKey,
            'credentials' => $creds,
        ]);
        $this->assertStringContainsString('123456', $output);
    }

    public function testEndpointsCommandWithClientSecrets()
    {
        $creds = $this->requireEnv('GOOGLE_CLIENT_SECRETS');
        $output = $this->runFunctionSnippet('make_request', [
            'host' => $this->host,
            'api_key' => $this->apiKey,
            'credentials' => $creds
        ]);

        $this->assertStringContainsString('id', $output);
        $this->assertStringContainsString('email', $output);
    }
}
