<?php
/**
 * Copyright 2018 Google LLC
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

namespace Google\Cloud\Samples\AppEngine\Symfony;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/DeploySymfonyTrait.php';

class DeployDoctrineTest extends TestCase
{
    use TestTrait;
    use DeploySymfonyTrait;

    public static function beforeDeploy()
    {
        // ensure logging output is displayed in phpunit
        self::$logger = new \Monolog\Logger('phpunit');

        $dbConn = self::requireEnv('SYMFONY_CLOUDSQL_CONNECTION_NAME');
        $dbName = self::requireEnv('SYMFONY_DB_DATABASE');
        $dbPass = self::requireEnv('SYMFONY_DB_PASSWORD');

        // Create the Symfony project in a temporary directory
        $symfonyDir = self::createSymfonyProject();
        self::updateKernelCacheAndLogDir($symfonyDir);

        // copy and set the proper env vars in app.yaml
        $appYaml = file_get_contents(__DIR__ . '/../app.yaml');
        $appYaml = str_replace('# DATABASE_URL', 'DATABASE_URL', $appYaml);
        $appYaml = str_replace('DB_PASSWORD', $dbPass, $appYaml);
        $appYaml = str_replace('INSTANCE_CONNECTION_NAME', $dbConn, $appYaml);
        $appYaml = str_replace('symfonydb', $dbName, $appYaml);

        file_put_contents($symfonyDir . '/app.yaml', $appYaml);
    }

    public function testHomepage()
    {
        // Access the blog top page
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(), 'top page status code');
        $this->assertContains(
            'Welcome to the <strong>Symfony Demo</strong> application',
            $resp->getBody()->getContents()
        );
    }

    public function testBlog()
    {
        // Access the blog top page
        $resp = $this->client->get('/en/blog/');
        $this->assertEquals('200', $resp->getStatusCode(), 'top page status code');
        $this->assertContains(
            'No posts found',
            $resp->getBody()->getContents()
        );
    }
}
