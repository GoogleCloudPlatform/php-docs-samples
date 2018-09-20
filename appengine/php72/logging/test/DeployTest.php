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
        $this->assertContains(
            'Logged INFO, WARNING, and ERROR log levels',
            $response->getBody()->getContents()
        );

        $this->verifyLog('This will show up as log level INFO', 'info');
        $this->verifyLog('This will show up as log level WARNING', 'warning');
        $this->verifyLog('This will show up as log level ERROR', 'error');
    }

    private function verifyLog($message, $level, $retryCount = 5)
    {
        $filter = sprintf(
            'resource.type = "gae_app" AND severity = "%s" AND logName = "%s"',
            strtoupper($level),
            sprintf('projects/%s/logs/app', self::$projectId)
        );
        $logOptions = [
            'pageSize' => 20,
            'resultLimit' => 20,
            'filter' => $filter,
        ];
        $logging = new LoggingClient();

        // Iterate through all elements
        $this->runEventuallyConsistentTest(function () use (
            $logging,
            $logOptions,
            $message
        ) {
            $logs = $logging->entries($logOptions);
            $matched = false;
            foreach ($logs as $log) {
                if ($log->info()['jsonPayload']['message'] == $message) {
                    $matched = true;
                    break;
                }
            }

            $this->assertTrue($matched);
        }, $retryCount, true);
    }
}
