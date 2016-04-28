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

use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use GuzzleHttp\Client;

class DeployTest extends \PHPUnit_Framework_TestCase
{
    use ExecuteCommandTrait;

    private $client;
    private static $version;

    private static function getVersion()
    {
        if (is_null(self::$version)) {
            $versionId = getenv('GOOGLE_VERSION_ID') ?: time();
            self::$version = "drupal8-" . $versionId;
        }

        return self::$version;
    }

    public static function getProjectId()
    {
        return getenv('GOOGLE_PROJECT_ID');
    }

    private static function getTargetDir()
    {
        $tmp = sys_get_temp_dir();
        $versionId = self::getVersion();
        $targetDir = sprintf('%s/%s', $tmp, $versionId);

        if (!file_exists($targetDir)) {
            mkdir($targetDir);
        }

        if (!is_writable($targetDir)) {
            throw new \Exception(sprintf('Cannot write to %s', $targetDir));
        }

        return $targetDir;
    }

    public static function setUpBeforeClass()
    {
        if (getenv('RUN_DEPLOYMENT_TESTS') !== 'true') {
            self::markTestSkipped(
                'To run this test, set RUN_DEPLOYMENT_TESTS env to "true".'
            );
        }

        self::$logger = new Logger('phpunit');

        // verify and set environment variables
        self::verifyEnvironmentVariables();
        $targetDir = self::getTargetDir();
        $projectId = self::getProjectId();
        $version = self::getVersion();

        // move into the target directory
        self::setWorkingDirectory($targetDir);
        $console = self::installDrupalConsole($targetDir);
        self::downloadAndInstallDrupal($targetDir, $console);
        self::deploy($projectId, $version, $targetDir);
    }

    private static function verifyEnvironmentVariables()
    {
        $envVars = [
            'GOOGLE_PROJECT_ID',
            'DRUPAL_ADMIN_USERNAME',
            'DRUPAL_ADMIN_PASSWORD',
            'DRUPAL_DATABASE_HOST',
            'DRUPAL_DATABASE_NAME',
            'DRUPAL_DATABASE_USER',
            'DRUPAL_DATABASE_PASS',
        ];
        foreach ($envVars as $envVar) {
            if (false === getenv($envVar)) {
                self::fail("Please set the ${envVar} environment variable");
            }
        }
    }

    private static function installDrupalConsole($targetDir)
    {
        $console = sprintf('%s/drupal', $targetDir);
        if (!file_exists($console)) {
            $cmd = sprintf(
                'curl https://drupalconsole.com/installer -Lo %s',
                $console
            );
            self::execute($cmd);
            self::execute(sprintf('chmod +x %s', $console));
        }

        return $console;
    }

    private static function downloadAndInstallDrupal($targetDir, $console)
    {
        $installFile = sprintf('%s/config/install_drupal8.yml', __DIR__);
        $config = Yaml::parse(file_get_contents($installFile));

        $configVars = [
            'db-host' => 'DRUPAL_DATABASE_HOST',
            'db-name' => 'DRUPAL_DATABASE_NAME',
            'db-user' => 'DRUPAL_DATABASE_USER',
            'db-pass' => 'DRUPAL_DATABASE_PASS',
            'account-name' => 'DRUPAL_ADMIN_USERNAME',
            'account-pass' => 'DRUPAL_ADMIN_PASSWORD',
        ];

        foreach ($configVars as $key => $name) {
            $config['commands'][1]['options'][$key] = getenv($name);
        }

        $newInstallFile = sprintf('%s/install_drupal8.yml', $targetDir);
        file_put_contents($newInstallFile, Yaml::dump($config));

        // install
        self::execute(sprintf('%s init', $console));
        self::execute(sprintf('%s chain --file=%s', $console, $newInstallFile));

        // move into the drupal directory so additional commands can be loaded
        self::setWorkingDirectory($targetDir . '/drupal8.test');

        // run setup commands
        self::execute(sprintf('%s theme:download bootstrap 8.x-3.0-beta2', $console));

        // this one is long, so create the process to extend the timeout
        $process = self::createProcess(sprintf('%s cache:rebuild all', $console));
        $process->setTimeout(300); // 5 minutes
        self::executeProcess($process);

        // install drupal dependencies
        self::execute('composer install');
        // this is to fix a PHP runtime bug
        // @TODO - FIX THIS!!
        self::execute('rm composer.*');

        // move the code for the sample to the new drupal installation
        $files = ['app.yaml', 'php.ini', 'Dockerfile', 'nginx-app.conf'];
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/drupal8.test/%s', $targetDir, $file);
            copy($source, $target);
        }
    }

    public static function deploy($projectId, $versionId, $targetDir)
    {
        for ($i = 0; $i <= 3; $i++) {
            $process = self::createProcess(
                "gcloud -q preview app deploy "
                . "--version $versionId "
                . "--project $projectId --no-promote -q "
                . "$targetDir/drupal8.test/app.yaml"
            );
            $process->setTimeout(60 * 30); // 30 minutes
            if (self::executeProcess($process, false)) {
                return;
            }
            self::$logger->warning('Retrying deployment');
        }
        self::fail('Deployment failed.');
    }

    public static function tearDownAfterClass()
    {
        for ($i = 0; $i <= 3; $i++) {
            $process = self::createProcess(
                'gcloud -q preview app versions delete --service default '
                 . self::getVersion()
                 . ' --project ' . self::getProjectId()
            );
            $process->setTimeout(600); // 10 minutes
            if (self::executeProcess($process, false)) {
                return;
            }
            self::$logger->warning('Retrying to delete the version');
        }
    }

    public function setUp()
    {
        $url = sprintf('https://%s-dot-%s.appspot.com/',
           self::getVersion(),
           self::getProjectId());
        $this->client = new Client(['base_uri' => $url]);
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
