<?php
/*
 * Copyright 2015 Google Inc. All Rights Reserved.
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
use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Yaml\Dumper;

/**
 * Class DeployTest
 */
class DeployTest extends E2eTest
{
    use TestTrait,
        AppEngineDeploymentTrait,
        GetConfigTrait;

    private static function beforeDeploy()
    {
        // Copy `app.yaml` and set environment variables
        $config = self::getConfig();
        $appYamlPath = __DIR__ . '/../../app.yaml';
        $appYaml = file_get_contents(__DIR__ . '/../app-e2e.yaml');
        file_put_contents($appYamlPath, str_replace(
            ['# ', 'CLOUDSQL_CONNECTION_NAME'],
            ['', $config['mysql_connection_name']],
            $appYaml
        ));
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
