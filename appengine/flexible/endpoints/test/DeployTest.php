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
// use Symfony\Component\Yaml\Yaml;

class DeployTest extends \PHPUnit_Framework_TestCase
{
    use AppEngineDeploymentTrait;

    public function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $clientId = getenv('GOOGLE_CLIENT_ID');
        $projectId = getenv('GOOGLE_PROJECT_ID');
        $serviceAccountEmail = getenv('GOOGLE_SERVICE_ACCOUNT_EMAIL');

        $swagger = str_replace(
            ['YOUR-PROJECT-ID', 'YOUR-CLIENT-ID', 'YOUR-SERVICE-ACCOUNT-EMAIL'],
            [$projectId, $clientId, $serviceAccountEmail],
            file_get_contents('swagger.yaml')
        );

        file_put_contents('swagger.yaml', $swagger);
    }

    public function testEcho()
    {
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
                'headers' => [
                    'CONTENT_TYPE' => 'application/json'
                ],
                'body' => json_encode([ 'message' => $message ])
            ]
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode((string) $response->getContent(), true);
        $this->assertNotNull($json);
        $this->assertArrayHasKey('message', $json);
        $this->assertEquals($message, $json['message']);
    }
}
