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

namespace Google\Cloud\Samples\AppEngine\Drupal;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\FileUtil;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;
    use ExecuteCommandTrait;

    public static function beforeDeploy()
    {
        // verify and set environment variables
        self::verifyEnvironmentVariables();

        // ensure logging output is displayed in phpunit
        self::$logger = new Logger('phpunit');

        // download, install, and deploy
        $tmpDir = sys_get_temp_dir() . '/test-' . FileUtil::randomName(8);
        self::downloadAndInstallDrupal($tmpDir);

        // set the directory in gcloud
        self::$gcloudWrapper->setDir($tmpDir);
    }

    private static function verifyEnvironmentVariables()
    {
        $envVars = [
            'GOOGLE_PROJECT_ID',
            'DRUPAL8_ADMIN_USERNAME',
            'DRUPAL8_ADMIN_PASSWORD',
            'DRUPAL8_DATABASE_HOST',
            'DRUPAL8_DATABASE_NAME',
            'DRUPAL8_DATABASE_USER',
            'DRUPAL8_DATABASE_PASS',
        ];
        foreach ($envVars as $envVar) {
            if (false === getenv($envVar)) {
                self::markTestSkipped("Please set the ${envVar} environment variable");
            }
        }
    }

    private static function downloadAndInstallDrupal($targetDir)
    {
        $console = __DIR__ . '/../vendor/bin/drush';

        $dbUrl = sprintf('mysql://%s:%s@%s/%s',
            getenv('DRUPAL8_DATABASE_USER'),
            getenv('DRUPAL8_DATABASE_PASS'),
            getenv('DRUPAL8_DATABASE_HOST'),
            getenv('DRUPAL8_DATABASE_NAME')
        );

        // download
        self::setWorkingDirectory(dirname($targetDir));
        $downloadCmd = sprintf('%s dl drupal --drupal-project-rename=%s',
            $console,
            basename($targetDir));
        self::execute($downloadCmd);

        // install
        self::setWorkingDirectory($targetDir);
        $installCmd = sprintf('%s site-install standard ' .
            '--db-url=%s --account-name=%s --account-pass=%s -y',
            $console,
            $dbUrl,
            getenv('DRUPAL8_ADMIN_USERNAME'),
            getenv('DRUPAL8_ADMIN_PASSWORD'));
        $process = self::createProcess($installCmd);
        $process->setTimeout(null);
        self::executeProcess($process);

        // this is to fix a PHP runtime bug
        // @TODO - FIX THIS!!
        self::execute('rm composer.*');

        // move the code for the sample to the new drupal installation
        $files = ['app.yaml'];
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/%s', $targetDir, $file);
            copy($source, $target);
        }
    }

    public function testContacts()
    {
        // Access the blog top page
        $resp = $this->client->get('/contact');
        $this->assertEquals(
            '200',
            $resp->getStatusCode(),
            'top page status code'
        );
        $content = $resp->getBody()->getContents();
        $this->assertContains('Website feedback', $content);
        $this->assertContains('Drupal', $content);
    }
}
