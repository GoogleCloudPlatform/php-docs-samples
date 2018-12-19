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

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use DeployLaravelTrait;
    use AppEngineDeploymentTrait;
    use EventuallyConsistentTestTrait;

    public static function beforeDeploy()
    {
        // ensure logging output is displayed in phpunit
        self::$logger = new \Monolog\Logger('phpunit');

        $tmpDir = self::createLaravelProject();
        copy(__DIR__ . '/../app.yaml', $tmpDir . '/app.yaml');
        self::addAppKeyToAppYaml($tmpDir);

        mkdir("$tmpDir/app/Logging", 0700, true);
        self::copyFiles([
            'routes/web.php',
            'config/logging.php',
            'app/Exceptions/Handler.php',
            'app/Logging/CreateCustomLogger.php',
        ], $tmpDir);

        // require google cloud logging and error reporting dependencies
        self::execute('composer require google/cloud-logging google/cloud-error-reporting');
    }

    public function testHomepage()
    {
        // Access the blog top page
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(), 'top page status code');
        $this->assertContains('Laravel', $resp->getBody()->getContents());
    }

    public function testNormalLog()
    {
        // bump up the retry count because logs can take a bit to show up
        $this->eventuallyConsistentRetryCount = 5;

        $logging = new LoggingClient([
            'projectId' => self::getProjectId()
        ]);

        $token = uniqid();
        // The routes are defined in routes/web.php
        $resp = $this->client->request('GET', "/log/$token", [
            'http_errors' => false
        ]);
        $this->assertEquals('200', $resp->getStatusCode(), 'log page status code');

        // 'app' is the default logname of our Stackdriver Logging integration.
        $logger = $logging->logger('app');
        $this->runEventuallyConsistentTest(function () use ($logger, $token) {
            $logs = $logger->entries([
                'pageSize' => 100,
                'orderBy' => 'timestamp desc',
                'resultLimit' => 100
            ]);
            $found = false;
            foreach ($logs as $log) {
                $info = $log->info();
                print($info['jsonPayload']['message'] . PHP_EOL);
                if (false !== strpos($info['jsonPayload']['message'], "token: $token")) {
                    $found = true;
                }
            }
            $this->assertTrue($found, "The log entry $token was not found");
        });
    }

    public function testErrorLog()
    {
        $this->eventuallyConsistentRetryCount = 5;
        $logging = new LoggingClient([
            'projectId' => self::getProjectId()
        ]);

        $token = uniqid();
        // The routes are defined in routes/web.php
        $resp = $this->client->request('GET', "/exception/$token", [
            'http_errors' => false
        ]);
        $this->assertEquals('500', $resp->getStatusCode(), 'exception page status code');

        // 'app-error' is the default logname of our Stackdriver Error Reporting integration.
        $logger = $logging->logger('app-error');
        $this->runEventuallyConsistentTest(function () use ($logger, $token) {
            $logs = $logger->entries([
                'pageSize' => 100,
                'orderBy' => 'timestamp desc',
                'resultLimit' => 100
            ]);
            $found = false;
            foreach ($logs as $log) {
                $info = $log->info();
                if (false !== strpos($info['jsonPayload']['message'], "token: $token")) {
                    $found = true;
                }
            }
            $this->assertTrue($found, 'The log entry was not found');
        });
    }
}
