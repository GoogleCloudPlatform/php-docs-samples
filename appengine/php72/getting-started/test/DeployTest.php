<?php
/*
 * Copyright 2018 Google LLC All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Bookshelf;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DeployTest
 */
class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;

    private static function beforeDeploy()
    {
        $bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        $connection = self::requireEnv('CLOUDSQL_CONNECTION_NAME');
        $dbUser = self::requireEnv('CLOUDSQL_USER');
        $dbPass = self::requireEnv('CLOUDSQL_PASSWORD');
        $dbName = getenv('CLOUDSQL_DATABASE_NAME') ?: 'bookshelf';

        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);

        $appYaml = Yaml::parse(file_get_contents($tmpDir . '/app.yaml'));
        $appYaml['env_variables']['GOOGLE_STORAGE_BUCKET'] = $bucketName;
        $appYaml['env_variables']['CLOUDSQL_CONNECTION_NAME'] = $connection;
        $appYaml['env_variables']['CLOUDSQL_USER'] = $dbUser;
        $appYaml['env_variables']['CLOUDSQL_PASSWORD'] = $dbPass;
        $appYaml['env_variables']['CLOUDSQL_DATABASE_NAME'] = $dbName;

        file_put_contents($tmpDir . '/app.yaml', Yaml::dump($appYaml));
    }

    public function testIndex()
    {
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(),
            'index status code');
        $this->assertContains('Book', (string) $resp->getBody(),
            'index content');
    }
}
