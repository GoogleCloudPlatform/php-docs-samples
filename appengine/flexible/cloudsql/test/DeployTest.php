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
namespace Google\Cloud\Samples\AppEngine\CloudSql;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use Symfony\Component\Yaml\Yaml;

class DeployTest extends \PHPUnit_Framework_TestCase
{
    use AppEngineDeploymentTrait;

    public function testIndex()
    {
        $resp = $this->client->request('GET', '/');

        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertContains("Last 10 visits:", (string) $resp->getBody());
    }

    public function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $connectionName = getenv('CLOUDSQL_CONNECTION_NAME');
        $user = getenv('MYSQL_USER');
        $password = getenv('MYSQL_PASSWORD');

        $appYaml = Yaml::parse(file_get_contents('app.yaml'));
        $appYaml['env_variables']['MYSQL_USER'] = $user;
        $appYaml['env_variables']['MYSQL_PASSWORD'] = $password;
        $appYaml['beta_settings']['cloud_sql_instances'] = $connectionName;
        $appYaml['env_variables']['MYSQL_DSN'] = str_replace(
            ['DATABASE', 'CONNECTION_NAME'],
            ['cloud_samples_tests_php', $connectionName],
            $appYaml['env_variables']['MYSQL_DSN']
        );

        file_put_contents('app.yaml', Yaml::dump($appYaml));
    }
}
