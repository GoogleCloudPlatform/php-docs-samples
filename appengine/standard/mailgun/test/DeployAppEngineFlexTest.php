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
namespace Google\Cloud\Samples\mailgun\test;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;

class DeployAppEngineFlexTest extends \PHPUnit_Framework_TestCase
{
    use AppEngineDeploymentTrait;

    public function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        FileUtil::copyDir(__DIR__ . '/../../../flexible/mailgun', $tmpDir);
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);
        $indexPhp = file_get_contents('index.php');
        $indexPhp = str_replace(
            'MAILGUN_DOMAIN_NAME',
            getenv('MAILGUN_DOMAIN_NAME'),
            $indexPhp
        );
        $indexPhp = str_replace(
            'MAILGUN_APIKEY',
            getenv('MAILGUN_APIKEY'),
            $indexPhp
        );
        file_put_contents('index.php', $indexPhp);
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
        $resp = $this->client->request('POST', '/', [
            'form_params' => [
                'recipient' => 'fake@example.com',
                'submit' => 'simple',
            ]
        ]);

        $this->assertEquals('200', $resp->getStatusCode(),
            'send message status code');
    }
}
