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
namespace Google\Cloud\Samples\PubSub\test;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;

class DeployAppEngineFlexTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public static function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        copy(__DIR__ . '/../app.yaml.flexible', $tmpDir . '/app.yaml');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);
    }

    public function testIndex()
    {
        // Access the modules app top page.
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(),
            'top page status code');
    }

    public function testSendMessage()
    {
        $resp = $this->client->request('POST', '/send_message', [
            'form_params' => [
                'message' => 'Good day!'
            ]
        ]);

        $this->assertEquals('204', $resp->getStatusCode(),
            '/send_message status code');
    }

    public function testReceiveMessage()
    {
        $resp = $this->client->request('POST', '/receive_message', [
                'body' => json_encode(['message' => ['data' => 'Bye.']]),
            ]
        );
        $this->assertEquals('200', $resp->getStatusCode(),
            '/receive_message status code');
    }
}
