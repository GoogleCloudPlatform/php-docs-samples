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
namespace Google\Cloud\Samples\AppEngine\Endpoints;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public static function setUpBeforeClass()
    {
        if (getenv('RUN_DEPLOYMENT_TESTS') !== 'true') {
            self::markTestSkipped(
                'To run this test, set RUN_DEPLOYMENT_TESTS env to "true".'
            );
        }
        if (!getenv('GOOGLE_ENDPOINTS_APIKEY')) {
            return self::markTestSkipped('Set the GOOGLE_ENDPOINTS_APIKEY environment variable');
        }
    }

    public static function beforeDeploy()
    {
        $clientId = getenv('GOOGLE_CLIENT_ID');
        $serviceAccountEmail = getenv('GOOGLE_SERVICE_ACCOUNT_EMAIL');
        if (empty($clientId) || empty($serviceAccountEmail)) {
            self::markTestSkipped('Please set GOOGLE_CLIENT_ID, GOOGLE_PROJECT_ID '
                . 'and GOOGLE_SERVICE_ACCOUNT_EMAIL');
        }
        $serviceName = getenv('GOOGLE_ENDPOINTS_SERVICE_NAME');
        $configId = getenv('GOOGLE_ENDPOINTS_CONFIG_ID');
        if (empty($serviceName) || empty($configId)) {
            self::markTestSkipped('Please set GOOGLE_ENDPOINTS_CONFIG_ID '
                . 'and GOOGLE_ENDPOINTS_SERVICE_NAME');
        }

        // copy the source files to a temp directory
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        // update the swagger file for our configuration
        $openapiYaml = str_replace(
            ['YOUR-PROJECT-ID', 'YOUR-CLIENT-ID', 'YOUR-SERVICE-ACCOUNT-EMAIL'],
            [self::getProjectId(), $clientId, $serviceAccountEmail],
            file_get_contents('openapi.yaml')
        );
        file_put_contents($tmpDir . '/openapi.yaml', $openapiYaml);

        // update app.yaml
        $appYaml = str_replace(
            ['ENDPOINTS SERVICE NAME', 'ENDPOINTS CONFIG ID'],
            [$serviceName, $configId],
            file_get_contents('app.yaml')
        );
        file_put_contents($tmpDir . '/app.yaml', $appYaml);
    }

    public function testEcho()
    {
        $apiKey = getenv('GOOGLE_ENDPOINTS_APIKEY');
        $message = <<<EOF
So if you're lost and on your own
You can never surrender
And if your path won't lead you home
You can never surrender
EOF;

        // create and send in JSON request
        $response = $this->client->request(
            'POST',
            '/echo',
            [
                'query' => ['key' => $apiKey],
                'body' => json_encode([ 'message' => $message ]),
                'headers' => ['content-type' => 'application/json'],
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode((string) $response->getBody(), true);
        $this->assertNotNull($json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($message, $json['message']);
    }

    public function test302()
    {
        // create and send in JSON request
        $response = $this->client->request(
            'POST',
            '/echo',
            ['exceptions' => false]
        );

        $this->assertEquals(401, $response->getStatusCode());
        $json = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('message', $json);
        $expectedString = 'unregistered callers';
        $this->assertContains($expectedString, $json['message']);
    }
}
