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

namespace Google\Cloud\Samples\AppEngine\Symfony;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\FileUtil;
use Symfony\Component\Yaml\Yaml;
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

        // build the symfony project
        $tmpDir = sys_get_temp_dir() . '/test-' . FileUtil::randomName(8);
        self::setWorkingDirectory($tmpDir);
        self::createSymfonyProject($tmpDir);
        self::addPostBuildCommands($tmpDir);

        // set the directory in gcloud and move there
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);
    }

    private static function verifyEnvironmentVariables()
    {
        $envVars = [
            'GOOGLE_PROJECT_ID',
            'SYMFONY_DATABASE_HOST',
            'SYMFONY_DATABASE_NAME',
            'SYMFONY_DATABASE_USER',
            'SYMFONY_DATABASE_PASS',
        ];
        foreach ($envVars as $envVar) {
            if (false === getenv($envVar)) {
                self::fail("Please set the ${envVar} environment variable");
            }
        }
    }

    private static function createSymfonyProject($targetDir)
    {
        // install
        $symfonyVersion = 'symfony/framework-standard-edition:^3.0';
        $cmd = sprintf('composer create-project --no-scripts %s %s', $symfonyVersion, $targetDir);
        $process = self::createProcess($cmd);
        $process->setTimeout(300); // 5 minutes
        self::executeProcess($process);

        // set the config from env vars
        $installFile = sprintf('%s/app/config/parameters.yml', $targetDir);
        $config = Yaml::parse(file_get_contents($installFile . '.dist'));

        $configVars = [
            'database_host' => 'SYMFONY_DATABASE_HOST',
            'database_name' => 'SYMFONY_DATABASE_NAME',
            'database_user' => 'SYMFONY_DATABASE_USER',
            'database_password' => 'SYMFONY_DATABASE_PASS',
        ];

        foreach ($configVars as $key => $name) {
            $config['parameters'][$key] = getenv($name);
        }

        file_put_contents($installFile, Yaml::dump($config));

        // move the code for the sample to the new drupal installation
        $files = ['app.yaml', 'nginx-app.conf'];
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/%s', $targetDir, $file);
            copy($source, $target);
        }
    }

    private static function addPostBuildCommands($targetDir)
    {
        $contents = file_get_contents($targetDir . '/composer.json');
        $json = json_decode($contents, true);
        $json['scripts']['post-install-cmd'] = ['chmod -R ug+w $APP_DIR/var'];
        file_put_contents($targetDir . '/composer.json', json_encode($json, JSON_PRETTY_PRINT));
    }

    public function testHomepage()
    {
        // Access the blog top page
        $resp = $this->client->get('/');
        $this->assertEquals(
            '200',
            $resp->getStatusCode(),
            'top page status code'
        );
        $content = $resp->getBody()->getContents();
        $this->assertContains('Your application is now ready', $content);
    }
}
