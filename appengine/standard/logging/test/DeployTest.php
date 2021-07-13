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
namespace Google\Cloud\Test\Logging;

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\Logging\LoggingClient;
use PHPUnit\Framework\TestCase;

/**
 * @group deploy
 */
class DeployTest extends TestCase
{
    use TestTrait;
    use AppEngineDeploymentTrait;
    use EventuallyConsistentTestTrait;

    public function testIndex()
    {
        // Access the modules app top page.
        $response = $this->client->get('');
        $this->assertEquals('200', $response->getStatusCode());
        $this->assertStringContainsString(
            'Logged INFO, WARNING, and ERROR log levels',
            $response->getBody()->getContents()
        );

        $this->verifyLog('This will show up as log level INFO', 'info', 5);

        // These should succeed if the above call has too.
        // Thus, they need fewer retries!
        $this->verifyLog('This will show up as log level WARNING', 'warning');
        $this->verifyLog('This will show up as log level ERROR', 'error');
    }

    private function verifyLog($message, $level, $retryCount = 3)
    {
        $fiveMinAgo = date(\DateTime::RFC3339, strtotime('-5 minutes'));
        $filter = sprintf(
            'resource.type="gae_app" severity="%s" logName="%s" timestamp>="%s"',
            strtoupper($level),
            sprintf('projects/%s/logs/app', self::$projectId),
            $fiveMinAgo
        );
        $logOptions = [
            'pageSize' => 50,
            'resultLimit' => 50,
            'filter' => $filter,
        ];
        $logging = new LoggingClient();

        // Iterate through all elements
        $this->runEventuallyConsistentTest(function () use (
            $logging,
            $logOptions,
            $message
        ) {
            // Concatenate all relevant log messages.
            $logs = $logging->entries($logOptions);
            $actual = '';
            foreach ($logs as $log) {
                $actual .= $log->info()['jsonPayload']['message'];
            }

            $this->assertStringContainsString($message, $actual);
        }, $retryCount, true);
    }
}
