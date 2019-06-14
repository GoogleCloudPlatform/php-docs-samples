<?php
/**
 * Copyright 2018 Google Inc.
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
namespace Google\Cloud\Test\CloudSql;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class DeployPostgresTest extends TestCase
{
    use AppEngineDeploymentTrait;

    public function testIndex()
    {
        $resp = $this->client->request('POST', '/', ['form_params' => [
            'name' => 'Automated Tests',
            'content' => $entry = sprintf('entry %s', date('Y-m-d')),
        ]]);
        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertContains($entry, (string) $resp->getBody());

        $resp = $this->client->request('GET', '/');

        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertContains($entry, (string) $resp->getBody());
    }

    public static function beforeDeploy()
    {
        if (!($connectionName = getenv('CLOUDSQL_CONNECTION_NAME_POSTGRES'))
            || (!$user = getenv('CLOUDSQL_USER'))
            || (!$database = getenv('CLOUDSQL_DATABASE'))
            || false === $password = getenv('CLOUDSQL_PASSWORD')) {
            self::markTestSkipped('Set the CLOUDSQL_CONNECTION_NAME_POSTGRES, CLOUDSQL_USER'
                . ' CLOUDSQL_DATABASE, and CLOUDSQL_PASSWORD environment variables');
        }

        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $appYamlContents = file_get_contents('app-postgres.yaml');
        $appYaml = Yaml::parse($appYamlContents);
        $appYaml['env_variables']['CLOUDSQL_USER'] = $user;
        $appYaml['env_variables']['CLOUDSQL_PASSWORD'] = $password;
        $appYaml['env_variables']['CLOUDSQL_DSN'] = str_replace(
            ['DATABASE', 'CONNECTION_NAME'],
            [$database, $connectionName],
            $appYaml['env_variables']['CLOUDSQL_DSN']
        );

        file_put_contents('app.yaml', Yaml::dump($appYaml));
    }
}
