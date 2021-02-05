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

require_once __DIR__ . '/DeployLaravelTrait.php';

class DeployStackdriverTest extends TestCase
{
    use DeployLaravelTrait;
    use AppEngineDeploymentTrait;
    use EventuallyConsistentTestTrait;

    public static function beforeDeploy()
    {
        // ensure logging output is displayed in phpunit
        self::$logger = new \Monolog\Logger('phpunit');

        $tmpDir = self::createLaravelProject();

        // Uncomment the stackdriver logging channel line in app.yaml
        file_put_contents(
            $tmpDir . '/app.yaml',
            str_replace(
                '# LOG_CHANNEL: stackdriver',
                'LOG_CHANNEL: stackdriver',
                file_get_contents(__DIR__ . '/../app.yaml')
            )
        );

        self::addAppKeyToAppYaml($tmpDir);

        mkdir("$tmpDir/app/Logging", 0700, true);
        self::copyFiles([
            'routes/web.php',
            'config/logging.php',
            'app/Exceptions/Handler.php',
            'app/Logging/CreateStackdriverLogger.php',
        ], $tmpDir);

        // require google cloud logging and error reporting dependencies
        self::execute('composer require google/cloud-logging google/cloud-error-reporting');
    }

    public function testLogging()
    {
        $logging = new LoggingClient([
            'projectId' => self::getProjectId()
        ]);

        $message = uniqid();
        // The routes are defined in routes/web.php
        $resp = $this->client->request('GET', "/log/$message", [
            'http_errors' => false
        ]);
        $this->assertEquals('200', $resp->getStatusCode(), 'log page status code');

        // 'app' is the default logname of our Stackdriver Logging integration.
        $logger = $logging->logger('app');
        $this->runEventuallyConsistentTest(function () use ($logger, $message) {
            $logs = $logger->entries([
                'pageSize' => 100,
                'orderBy' => 'timestamp desc',
                'resultLimit' => 100
            ]);
            $found = false;
            foreach ($logs as $log) {
                $info = $log->info();
                if (false !== strpos($info['jsonPayload']['message'], "message: $message")) {
                    $found = true;
                }
            }
            $this->assertTrue($found, "The log entry $message was not found");
        }, $eventuallyConsistentRetryCount = 5);
    }

    public function testErrorReporting()
    {
        $logging = new LoggingClient([
            'projectId' => self::getProjectId()
        ]);

        $message = uniqid();
        // The routes are defined in routes/web.php
        $resp = $this->client->request('GET', "/exception/$message", [
            'http_errors' => false
        ]);
        $this->assertEquals('500', $resp->getStatusCode(), 'exception page status code');

        // 'app-error' is the default logname of our Stackdriver Error Reporting integration.
        $logger = $logging->logger('app-error');
        $this->runEventuallyConsistentTest(function () use ($logger, $message) {
            $logs = $logger->entries([
                'pageSize' => 100,
                'orderBy' => 'timestamp desc',
                'resultLimit' => 100
            ]);
            $found = false;
            foreach ($logs as $log) {
                $info = $log->info();
                if (false !== strpos($info['jsonPayload']['message'], "message: $message")) {
                    $found = true;
                }
            }
            $this->assertTrue($found, 'The log entry was not found');
        }, $eventuallyConsistentRetryCount = 5);
    }
}
