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

namespace Google\Cloud\Samples\AppEngine\Laravel;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\FileUtil;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class DeployDatabaseSessionTest extends TestCase
{
    use AppEngineDeploymentTrait;
    use ExecuteCommandTrait;

    public static function beforeDeploy()
    {
        // verify and set environment variables
        self::verifyEnvironmentVariables();

        // ensure logging output is displayed in phpunit
        self::$logger = new Logger('phpunit');

        $tmpDir = sys_get_temp_dir() . '/test-' . FileUtil::randomName(8);

        // move into the target directory
        self::setWorkingDirectory($tmpDir);
        self::createLaravelProject($tmpDir);
        self::addPostDeployCommands($tmpDir);

        // set the directory in gcloud and move there
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);
    }

    private static function verifyEnvironmentVariables()
    {
        $envVars = [
            'LARAVEL_CLOUDSQL_CONNECTION_NAME',
            'LARAVEL_DB_DATABASE',
            'LARAVEL_DB_USERNAME',
            'LARAVEL_DB_PASSWORD',
        ];
        foreach ($envVars as $envVar) {
            if (false === getenv($envVar)) {
                self::fail("Please set the ${envVar} environment variable");
            }
        }
    }

    private static function createLaravelProject($targetDir)
    {
        // install
        $laravelPackage = 'laravel/laravel';
        $cmd = sprintf('composer create-project --no-scripts %s %s', $laravelPackage, $targetDir);
        $process = self::createProcess($cmd);
        $process->setTimeout(300); // 5 minutes
        self::executeProcess($process);

        // copy and set the proper env vars in app.yaml
        $appYaml = str_replace([
            'YOUR_APP_KEY',
            'YOUR_CLOUDSQL_CONNECTION_NAME',
            'YOUR_DB_DATABASE',
            'YOUR_DB_USERNAME',
            'YOUR_DB_PASSWORD',
        ], [
            self::execute('php artisan key:generate --show --no-ansi'),
            getenv('LARAVEL_CLOUDSQL_CONNECTION_NAME'),
            getenv('LARAVEL_DB_DATABASE'),
            getenv('LARAVEL_DB_USERNAME'),
            getenv('LARAVEL_DB_PASSWORD'),
        ], file_get_contents(__DIR__ . '/../app-dbsessions.yaml'));
        file_put_contents($targetDir . '/app.yaml', $appYaml);
    }

    private static function addPostDeployCommands($targetDir)
    {
        $contents = file_get_contents($targetDir . '/composer.json');
        $json = json_decode($contents, true);
        $json['scripts']['post-install-cmd'] = [
            'chmod -R 755 bootstrap\/cache',
            'php artisan cache:clear',
        ];
        $newContents = json_encode($json, JSON_PRETTY_PRINT);
        file_put_contents($targetDir . '/composer.json', $newContents);
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
        $this->assertContains('Laravel', $content);
    }
}
