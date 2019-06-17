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

namespace Google\Cloud\Samples\AppEngine\Laravel;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/DeployLaravelTrait.php';

class DeployTest extends TestCase
{
    use DeployLaravelTrait;
    use AppEngineDeploymentTrait;

    public static function beforeDeploy()
    {
        // ensure logging output is displayed in phpunit
        self::$logger = new \Monolog\Logger('phpunit');

        $tmpDir = self::createLaravelProject();
        copy(__DIR__ . '/../app.yaml', $tmpDir . '/app.yaml');
        self::addAppKeyToAppYaml($tmpDir);
    }

    public function testHomepage()
    {
        // Access the blog top page
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(), 'top page status code');
        $this->assertContains('Laravel', $resp->getBody()->getContents());
    }
}
