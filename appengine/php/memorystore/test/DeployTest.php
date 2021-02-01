<?php
/**
 * Copyright 2019 Google Inc.
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
namespace Google\Cloud\Test\Memorystore;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class DeployTest extends TestCase
{
    use TestTrait;
    use AppEngineDeploymentTrait;

    public function testIndex()
    {
        $resp = $this->client->request('GET', '/');

        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertRegExp('/Visitor number: \d+/', (string) $resp->getBody());
    }

    public static function beforeDeploy()
    {
        $host = self::requireEnv('REDIS_HOST');
        $connectorName = self::requireEnv('GOOGLE_VPC_ACCESS_CONNECTOR_NAME');

        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $appYaml = Yaml::parse(file_get_contents('app.yaml'));
        $appYaml['env_variables']['REDIS_HOST'] = $host;
        if ($port = getenv('REDIS_PORT')) {
            $appYaml['env_variables']['REDIS_PORT'] = $port;
        }
        $appYaml['vpc_access_connector']['name'] = $connectorName;

        file_put_contents('app.yaml', Yaml::dump($appYaml));
    }

    private static function doDeploy()
    {
        // Ensure we use gcloud "beta" deploy
        return self::$gcloudWrapper->deploy(['release_version' => 'beta']);
    }
}
