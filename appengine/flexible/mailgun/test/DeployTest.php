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
namespace Google\Cloud\Samples\AppEngine\Mailgun;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public static function beforeDeploy()
    {
        // set your Mailgun domain name and API key
        $mailgunDomain = getenv('MAILGUN_DOMAIN');
        $mailgunApiKey = getenv('MAILGUN_APIKEY');

        if (empty($mailgunDomain) || empty($mailgunApiKey)) {
            self::markTestSkipped('set the MAILGUN_DOMAIN and MAILGUN_APIKEY environment variables');
        }

        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $indexPhp = file_get_contents('index.php');
        $indexPhp = str_replace(
            'MAILGUN_DOMAIN',
            $mailgunDomain,
            $indexPhp
        );
        $indexPhp = str_replace(
            'MAILGUN_APIKEY',
            $mailgunApiKey,
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
