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

class DeployTest extends \PHPUnit_Framework_TestCase
{
    use AppEngineDeploymentTrait;
    use ExecuteCommandTrait;

    public static function beforeDeploy()
    {
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

    private static function createLaravelProject($targetDir)
    {
        // install
        $laravelPackage = 'laravel/laravel';
        $cmd = sprintf('composer create-project --no-scripts %s %s', $laravelPackage, $targetDir);
        $process = self::createProcess($cmd);
        $process->setTimeout(300); // 5 minutes
        self::executeProcess($process);

        // copy in the app.yaml
        copy(__DIR__ . '/../app.yaml', $targetDir . '/app.yaml');

        // copy over the base .env file
        self::execute('cp .env.example .env');

        // generate the secret
        self::execute('php artisan key:generate');
    }

    private static function addPostDeployCommands($targetDir)
    {
        $contents = file_get_contents($targetDir . '/composer.json');
        $json = json_decode($contents, true);
        $json['scripts']['post-deploy-cmd'] = [
            'chmod -R 755 bootstrap\/cache',
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
