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
namespace Google\Cloud\Samples\twilio\test;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $appYaml = Yaml::parse(file_get_contents('app.yaml'));
        $appYaml['env_variables']['TWILIO_ACCOUNT_SID'] =
            getenv('TWILIO_ACCOUNT_SID');
        $appYaml['env_variables']['TWILIO_AUTH_TOKEN'] =
            getenv('TWILIO_AUTH_TOKEN');
        $appYaml['env_variables']['TWILIO_FROM_NUMBER'] =
            getenv('TWILIO_FROM_NUMBER');
        file_put_contents('app.yaml', Yaml::dump($appYaml));
    }

    public function testReceiveCall()
    {
        $response = $this->client->request('POST', '/call/receive');
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody()->getContents();
        $this->assertContains('<Say>Hello from Twilio!</Say>', $body);
    }

    public function testReceiveSms()
    {
        $params = [
            'From' => '16505551212',
            'Body' => 'This is the best text message ever sent.'
        ];
        $response = $this->client->request('POST', '/sms/receive', [
            'form_params' => $params,
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody()->getContents();
        $this->assertContains($params['From'], $body);
        $this->assertContains($params['Body'], $body);
    }

    public function testSendSms()
    {
        $params = [
            'to' => '16505551212',
        ];
        $response = $this->client->request('POST', '/sms/send', [
            'form_params' => $params,
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
