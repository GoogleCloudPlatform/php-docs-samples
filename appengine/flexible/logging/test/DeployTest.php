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
namespace Google\Cloud\Test;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\Logging\LoggingClient;

use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    use AppEngineDeploymentTrait;
    use EventuallyConsistentTestTrait;

    public function setUp()
    {
        if (!getenv('TRAVIS_SECURE_ENV_VARS')) {
            $this->markTestSkipped('No secret available');
            // TODO: Make the test runnable without secret
        }
    }
    public function testIndex()
    {
        // Access the modules app top page.
        $resp = $this->client->get('/');
        $this->assertEquals('200', $resp->getStatusCode(),
            'top page status code');

        $this->assertContains("Logs:", (string) $resp->getBody());
    }
    public function testAsyncLog()
    {
        $token = uniqid();
        $resp = $this->client->get("/async_log?token=$token");
        $this->assertEquals('200', $resp->getStatusCode(),
            'async_log status code');
        $logging = new LoggingClient(
            ['projectId' => getenv('GOOGLE_PROJECT_ID')]
        );
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
                    if (strpos($token, $info['jsonPayload']['message']) !== 0) {
                        $found = true;
                    }
                }
                $this->assertTrue($found, 'The log entry was not found');
            });
    }
}
