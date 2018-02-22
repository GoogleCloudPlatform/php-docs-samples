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

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\FileUtil;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;
    use ExecuteCommandTrait;
    use EventuallyConsistentTestTrait;

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
        // add cloud libraries
        $cmd = sprintf(
            'composer --working-dir=%s require google/cloud-logging '
            . 'google/cloud-error-reporting',
            $targetDir
        );
        $process = self::createProcess($cmd);
        $process->setTimeout(300); // 5 minutes
        self::executeProcess($process);

        // copy in the app.yaml and add the app key.
        $appYaml = str_replace([
            'YOUR_APP_KEY',
        ], [
            self::execute('php artisan key:generate --show --no-ansi'),
        ], file_get_contents(__DIR__ . '/../app.yaml'));
        file_put_contents($targetDir . '/app.yaml', $appYaml);
        // move the code for the sample to the new laravel installation
        mkdir("$targetDir/app/Logging", 0700, true);
        $files = [
            'routes/web.php',
            'config/logging.php',
            'app/Exceptions/Handler.php',
            'app/Logging/CreateCustomLogger.php',
        ];
        foreach ($files as $file) {
            $source = sprintf('%s/../%s', __DIR__, $file);
            $target = sprintf('%s/%s', $targetDir, $file);
            copy($source, $target);
        }
    }

    private static function addPostDeployCommands($targetDir)
    {
        $contents = file_get_contents($targetDir . '/composer.json');
        $json = json_decode($contents, true);
        $json['scripts']['post-install-cmd'] = [
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

    public function testNormalLog()
    {
        // Access a page erroring with 500
        $token = uniqid();
        // The routes are defined in routes/web.php
        $path = "/log/$token";
        $resp = $this->client->request('GET', $path, ['http_errors' => false]);
        $this->assertEquals(
            '200',
            $resp->getStatusCode(),
            'log page status code'
        );
        $logging = new LoggingClient(
            ['projectId' => getenv('GOOGLE_PROJECT_ID')]
        );
        // 'app' is the default logname of our Stackdriver Logging
        // integration.
        $logger = $logging->logger('app');

        $this->runEventuallyConsistentTest(
            function () use ($logger, $token) {
                $logs = $logger->entries([
                    'pageSize' => 100,
                    'orderBy' => 'timestamp desc',
                    'resultLimit' => 100
                ]);
                $found = false;
                foreach ($logs as $log) {
                    $info = $log->info();
                    if (strpos("token: $token", $info['jsonPayload']['message']) !== 0) {
                        $found = true;
                    }
                }
                $this->assertTrue($found, 'The log entry was not found');
            });
    }

    public function testErrorLog()
    {
        // Access a page erroring with 500
        $token = uniqid();
        // The routes are defined in routes/web.php
        $path = "/exception/$token";
        $resp = $this->client->request('GET', $path, ['http_errors' => false]);
        $this->assertEquals(
            '500',
            $resp->getStatusCode(),
            'exception page status code'
        );
        $logging = new LoggingClient(
            ['projectId' => getenv('GOOGLE_PROJECT_ID')]
        );
        // 'app-error' is the default logname of our Stackdriver Error
        // Reporting integration.
        $logger = $logging->logger('app-error');

        $this->runEventuallyConsistentTest(
            function () use ($logger, $token) {
                $logs = $logger->entries([
                    'pageSize' => 100,
                    'orderBy' => 'timestamp desc',
                    'resultLimit' => 100
                ]);
                $found = false;
                foreach ($logs as $log) {
                    $info = $log->info();
                    if (strpos("token: $token", $info['jsonPayload']['message']) !== 0) {
                        $found = true;
                    }
                }
                $this->assertTrue($found, 'The log entry was not found');
            });
    }
}
