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

namespace Google\Cloud\Samples\AppEngine\Php72\WordPress;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use ExecuteCommandTrait;
    use AppEngineDeploymentTrait;

    private static $commandFile = __DIR__ . '/../vendor/bin/wp-gae';

    public static function beforeDeploy()
    {
        if (!($projectId = getenv('GOOGLE_PROJECT_ID'))
            || !($dbInstance = getenv('WORDPRESS_DB_INSTANCE_NAME'))
            || !($dbUser = getenv('WORDPRESS_DB_USER'))
            || !($dbPassword = getenv('WORDPRESS_DB_PASSWORD'))) {
            self::markTestSkipped('You must set GOOGLE_PROJECT_ID, '
                . 'WORDPRESS_INSTANCE_NAME, and WORDPRESS_DB_PASSWORD');
        }

        $dir = sprintf('%s/wp-gae-php72-%s', sys_get_temp_dir(), time());
        self::runCommand('create', [
            '--dir' => $dir,
            '--project_id' => $projectId,
            '--db_instance' => $dbInstance,
            '--db_user' => $dbUser,
            '--db_password' => $dbPassword,
            '--db_name' => getenv('WORDPRESS_DB_NAME') ?: 'wordpress_php72',
        ]);

        self::$gcloudWrapper->setDir($dir);
    }

    public function testIndex()
    {
        // Access the blog top page
        $resp = $this->client->get('');
        $this->assertEquals('200', $resp->getStatusCode());
        $this->assertContains(
            'It looks like your WordPress installation is running on App '
            . 'Engine for PHP 7.2!',
            $resp->getBody()->getContents()
        );
    }
}
