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

namespace Google\Cloud\Samples\AppEngine\Symfony;

use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/DeploySymfonyTrait.php';

class DeployTest extends TestCase
{
    use DeploySymfonyTrait;
    use EventuallyConsistentTestTrait;

    public static function beforeDeploy()
    {
        // ensure logging output is displayed in phpunit
        self::$logger = new \Monolog\Logger('phpunit');

        // build the symfony project
        $symfonyDir = self::createSymfonyProject();
        self::updateKernelCacheAndLogDir($symfonyDir);

        // Copy files for logging, exception handling, and the controller for testing.
        self::copyFiles([
            'config/packages/prod/monolog.yaml',
            'src/Controller/LoggingController.php',
            'src/EventSubscriber/ExceptionSubscriber.php',
        ], $symfonyDir);

        // require google cloud logging and error reporting dependencies
        self::execute('composer require google/cloud-logging google/cloud-error-reporting');
    }

    public function testHomepage()
    {
        // Access the symfony default page
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(), 'top page status code');
        $this->assertContains(
            'Welcome to the <strong>Symfony Demo</strong> application',
            $resp->getBody()->getContents()
        );
    }

    public function testLogging()
    {
        // bump up the retry count because logs can take a bit to show up
        $this->eventuallyConsistentRetryCount = 5;

        $logging = new LoggingClient([
            'projectId' => self::getProjectId()
        ]);

        $token = uniqid();
        // The routes are defined in routes/web.php
        $resp = $this->client->request('GET', "/en/logging/notice/$token", [
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
                if (false !== strpos($info['jsonPayload']['message'], "token: $token")) {
                    $found = true;
                }
            }
            $this->assertTrue($found, "The log entry $token was not found");
        });
    }

    public function testErrorReporting()
    {
        $this->eventuallyConsistentRetryCount = 5;
        $logging = new LoggingClient([
            'projectId' => self::getProjectId()
        ]);

        $token = uniqid();
        // The routes are defined in routes/web.php
        $resp = $this->client->request('GET', "/en/logging/exception/$token", [
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
